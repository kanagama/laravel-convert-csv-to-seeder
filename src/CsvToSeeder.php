<?php

namespace App\Libraries;

use App\Libraries\Consts\ErrorMsg;
use Carbon\CarbonImmutable;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Kanagama\CsvReader\CsvReader;

/**
 * @author k.nagama <k.nagama0632@gmail.com>
 */
class CsvToSeeder
{
    /**
     * CSVパス
     *
     * @var string|null
     */
    private ?string $csvPath;

    /**
     * 保存先モデル
     *
     * @var string|null
     */
    private ?string $model;

    /**
     * モデルインスタンス
     *
     * @var Model|null
     */
    private ?Model $instance;

    /**
     * 割当
     *
     * @var array
     */
    private array $mappings;

    /**
     * デリミタ
     *
     * @var string
     */
    private string $delimiter;

    /**
     * created_at, updated_at 追記フラグ
     *
     * @var bool
     */
    private bool $timestamps;

    /**
     * created_at 別名
     *
     * @var string
     */
    private string $created_at;

    /**
     * updated_at 別名
     *
     * @var string
     */
    private string $updated_at;

    /**
     * 1行目をスキップする
     *
     * @var bool
     */
    private bool $header;

    /**
     * インポート件数
     *
     * @var int|null
     */
    private ?int $limit;

    /**
     * 指定した行番号から読み込む
     *
     * @var int|null
     */
    private ?int $offset;

    /**
     * トランザクション利用
     *
     * @var bool
     */
    private bool $transaction;

    private int $counter;

    /**
     *
     */
    public function __construct()
    {
        $this->clear();
    }

    /**
     * @return self
     */
    public static function init(): self
    {
        return new self();
    }

    /**
     * @param  string  $csvPath
     * @return self
     */
    public function csvPath(string $csvPath): self
    {
        if (empty($csvPath) || !file_exists($csvPath)) {
            throw new FileNotFoundException(ErrorMsg::FILE_NOT_FOUND);
        }

        $this->csvPath = $csvPath;
        return $this;
    }

    /**
     * @param  string  $table
     * @return self
     */
    public function model(string $model): self
    {
        if (empty($model) || !is_string($model) || !class_exists($model)) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->model = $model;
        $this->instance = app()->make($model);
        return $this;
    }

    /**
     * @param  string  $delimiter
     */
    public function delimiter(string $delimiter = ','): self
    {
        if (empty($delimiter) || !strlen($delimiter)) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->delimiter = $delimiter;
        return $this;
    }

    /**
     * @param  int  $limit
     * @return self
     */
    public function limit(int $limit): self
    {
        if ($limit < 1) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->limit = $limit;
        return $this;
    }

    /**
     * @param  int  $offset
     * @return self
     */
    public function offset(int $offset): self
    {
        if ($offset < 0) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->offset = $offset;
        return $this;
    }

    /**
     * @param  bool  $header
     * @return self
     */
    public function header(bool $header = true): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * @param  bool  $transaction
     * @return self
     */
    public function transaction(bool $transaction = true): self
    {
        $this->transaction = $transaction;

        return $this;
    }

    /**
     * @param  bool  $timestamps
     */
    public function timestamps(bool $timestamps = true): self
    {
        $this->timestamps = $timestamps;
        return $this;
    }

    /**
     * @param  string  $createdAt
     * @return self
     */
    public function createdAt(string $createdAt): self
    {
        if (empty($createdAt)) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * @param  string  $updatedAt
     * @return self
     */
    public function updatedAt(string $updatedAt): self
    {
        if (empty($updatedAt)) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->updated_at = $updatedAt;
        return $this;
    }

    /**
     * @param  array  $mappings
     * @return self
     */
    public function mappings(array $mappings): self
    {
        if (empty($mappings)) {
            throw new ValidationException(ErrorMsg::VALIDATE_ERROR);
        }

        $this->mappings = $mappings;
        return $this;
    }

    /**
     * @return void
     */
    public function insert()
    {
        $iterator = (new CsvReader(
            $this->getCsvPath(),
            $this->getDelimiter(),
            $this->getHeader()
        ))->readline();

        /** \Illuminate\Database\Connection */
        $connection = DB::connection($this->getInstance()->getConnectionName());

        $connection->statement('SET FOREIGN_KEY_CHECKS=0;');

        if ($this->getTransaction()) {
            $connection->beginTransaction();
        }

        try {
            $this->insertCsv($iterator);

            if ($this->getTransaction()) {
                $connection->commit();
            }
        } catch (\Throwable $e) {
            if ($this->getTransaction()) {
                $connection->rollback();
            }
        } finally {
            $connection->statement('SET FOREIGN_KEY_CHECKS=1;');

            $this->clear();
        }
    }

    /**
     * @return void
     */
    private function insertCsv($iterator)
    {
        $mappings = $this->getMappings();
        foreach ($iterator as $count => $line) {
            // offset の数字までスキップ
            if ($this->checkOffset($count)) {
                continue;
            }

            $saves = [];
            foreach ($line as $key => $row) {
                // マッピングされてて存在しなければ次へ
                if (empty($mappings[$key])) {
                    continue;
                }

                $saves[$mappings[$key]] = null;
                if (!empty($row) || $row === '0') {
                    $saves[$mappings[$key]] = trim($row);
                }
            }

            if ($this->getTimestamps()) {
                $saves[$this->getCreatedAt()] = CarbonImmutable::now()->toIso8601String();
                $saves[$this->getUpdatedAt()] = CarbonImmutable::now()->toIso8601String();
            }

            if ($saves) {
                $this->getInstance()->fill($saves)->saveOrFail();
                // インスタンス初期化
                $this->model($this->model);
            }

            $this->counter++;
            // limit を超えたら終了
            if ($this->checkLimit()) {
                break;
            }
        }
    }

    /**
     * @return bool
     */
    private function checkOffset(int $count): bool
    {
        return (
            !is_null($this->getOffset())
            &&
            $this->getOffset() >= $count
        );
    }

    /**
     * @return bool
     */
    private function checkLimit(): bool
    {
        return (
            !is_null($this->getLimit())
            &&
            $this->getLimit() <= $this->counter
        );
    }

    /**
     * @return string
     */
    private function getCsvPath(): string
    {
        return $this->csvPath;
    }

    /**
     * @return Model
     */
    public function getInstance(): Model
    {
        if (empty($this->instance)) {
            throw new ValidationException(ErrorMsg::VALIDATE_NOT_MODEL);
        }

        return $this->instance;
    }

    /**
     * @return string
     */
    private function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * @return int|null
     */
    private function getLimit(): ?int
    {
        return $this->limit;
    }

    /**
     * @return int|null
     */
    private function getOffset(): ?int
    {
        return $this->offset;
    }

    /**
     * @return bool
     */
    private function getHeader(): bool
    {
        return $this->header;
    }

    /**
     * @return bool
     */
    private function getTransaction(): bool
    {
        return $this->transaction;
    }

    /**
     * @return bool
     */
    public function getTimestamps(): bool
    {
        return $this->timestamps;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->created_at;
    }

    /**
     * @return string
     */
    private function getUpdatedAt(): string
    {
        return $this->updated_at;
    }

    /**
     * @return array
     */
    private function getMappings(): array
    {
        if (!empty($this->mappings)) {
            return $this->mappings;
        }

        throw new ValidationException(ErrorMsg::VALIDATE_NOT_MAPPINGS);
    }

    /**
     * プロパティを初期化する
     *
     * @return self
     */
    private function clear()
    {
        $this->counter = 0;
        $this->csvPath = null;
        $this->model = null;
        $this->instance = null;
        $this->mappings = [];
        $this->delimiter = ',';
        $this->timestamps = false;
        $this->created_at = 'created_at';
        $this->updated_at = 'updated_at';
        $this->header = false;
        $this->limit = null;
        $this->offset = null;
        $this->transaction = false;
    }
}
