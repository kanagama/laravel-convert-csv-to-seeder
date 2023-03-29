<?php

namespace Kanagama\CsvToSeeder\Tests\Unit;

use Kanagama\CsvToSeeder\CsvToSeeder;
use Kanagama\CsvToSeeder\Consts\ErrorMsg;
use Kanagama\CsvToSeeder\Tests\Models\User;
use Kanagama\CsvToSeeder\Tests\TestCase;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use InvalidArgumentException;
use ReflectionClass;

class CsvToSeederTest extends TestCase
{
    /**
     * @test
     */
    public function csvPathメソッドでcsvPathプロパティに値がセットされる()
    {
        $csvPath = __DIR__ . '/../File/test.csv';

        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('csvPath');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init()
            ->csvPath($csvPath);

        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, $csvPath);
    }

    /**
     * @test
     */
    public function csvPathメソッドで正しくないパスを渡すと例外()
    {
        $this->expectException(FileNotFoundException::class);
        $this->expectExceptionMessage(ErrorMsg::FILE_NOT_FOUND);

        CsvToSeeder::init()->csvPath('');
    }

    /**
     * @test
     */
    public function modelとinstanceが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        // model が設定されているかチェック
        $property = $reflector->getProperty('model');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init()
            ->model(User::class);

        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 'Kanagama\CsvToSeeder\Tests\Models\User');

        // instance が設定されているかチェック
        $property = $reflector->getProperty('instance');
        $property->setAccessible(true);

        $result = $property->getValue($csvToSeeder);
        $this->assertInstanceOf(User::class, $result);
    }

    /**
     * @test
     */
    public function modelが存在しなければ例外()
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->model('\App\Models\User');
    }

    /**
     * @test
     */
    public function delimiterが正常に設定される()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('delimiter');
        $property->setAccessible(true);

        // デフォルトでも設定済み
        $csvToSeeder = CsvToSeeder::init();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals(',', $result);

        // 空白デリミタでもOK
        $csvToSeeder->delimiter(' ');
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals(' ', $result);
    }

    /**
     * @test
     */
    public function delimiterに空は設定できない()
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->delimiter('');
    }

    /**
     * @test
     */
    public function limitが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('limit');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init()
            ->limit(1);

        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 1);
    }

    /**
     * @test
     *
     * @dataProvider limitProvider
     */
    public function limitは0とマイナスを受け付けない(int $limit)
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->limit($limit);
    }

    /**
     * @return array
     */
    public function limitProvider(): array
    {
        return [
            [
                'limit' => 0,
            ],
            [
                'limit' => -1,
            ]
        ];
    }

    /**
     * @test
     */
    public function offsetが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('offset');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init()
            ->offset(0);

        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 0);
    }

    /**
     * @test
     */
    public function offsetはマイナスを受け付けない()
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->offset(-1);
    }

    /**
     * @test
     */
    public function headerが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('header');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, false);

        $csvToSeeder->header();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, true);
    }

    /**
     * @test
     */
    public function timestampsが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('timestamps');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, false);

        $csvToSeeder->timestamps();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, true);
    }

    /**
     * @test
     */
    public function createdAtが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('created_at');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 'created_at');

        $csvToSeeder->createdAt('created');
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 'created');
    }

    /**
     * @test
     */
    public function createdAtは空白を受け付けない()
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->createdAt('');
    }

    /**
     * @test
     */
    public function updatedAtが正常に設定できる()
    {
        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('updated_at');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init();
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 'updated_at');

        $csvToSeeder->updatedAt('modified');
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, 'modified');
    }

    /**
     * @test
     */
    public function updatedAtは空白を受け付けない()
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->updatedAt('');
    }

    /**
     * @test
     */
    public function mappingsが正常に設定できる()
    {
        $mappings = [
            'name',
            'view_flg',
        ];

        $reflector = new ReflectionClass(CsvToSeeder::class);

        $property = $reflector->getProperty('mappings');
        $property->setAccessible(true);

        $csvToSeeder = CsvToSeeder::init()->mappings($mappings);
        $result = $property->getValue($csvToSeeder);
        $this->assertEquals($result, $mappings);
    }

    /**
     * @test
     */
    public function mappingsは空の配列を受け付けない()
    {
        $this->expectException(InvalidArgumentException::class);

        CsvToSeeder::init()->mappings([]);
    }

    /**
     * @test
     */
    public function clearでCsvToSeederインスタンスが初期化される()
    {
        $csvToSeeder = CsvToSeeder::init();

        $reflector = new ReflectionClass(CsvToSeeder::class);

        $method = $reflector->getMethod('clear');
        $method->setAccessible(true);

        // counter が初期化されている
        $counter = $reflector->getProperty('counter');
        $counter->setAccessible(true);
        $this->assertEquals($counter->getValue($csvToSeeder), 0);
        // counter に値を格納
        $counter->setValue($csvToSeeder, 10);
        $this->assertEquals($counter->getValue($csvToSeeder), 10);

        // csvPath が初期化されている
        $csvPath = $reflector->getProperty('csvPath');
        $csvPath->setAccessible(true);
        $this->assertNull($csvPath->getValue($csvToSeeder));
        // csvPath に値を格納
        $csvToSeeder->csvPath(__DIR__ . '/../File/test.csv');
        $this->assertEquals($csvPath->getValue($csvToSeeder), __DIR__ . '/../File/test.csv');

        // model が初期化されている
        $model = $reflector->getProperty('model');
        $model->setAccessible(true);
        $this->assertNull($model->getValue($csvToSeeder));
        // instance が初期化されている
        $instance = $reflector->getProperty('instance');
        $instance->setAccessible(true);
        $this->assertNull($instance->getValue($csvToSeeder));

        // model に値を格納すると、model と instance に値が格納される
        $csvToSeeder->model(User::class);
        $this->assertEquals('Kanagama\CsvToSeeder\Tests\Models\User', $model->getValue($csvToSeeder));
        $this->assertInstanceOf(User::class, $instance->getValue($csvToSeeder));

        // mappings が初期化されている
        $mappings = $reflector->getProperty('mappings');
        $mappings->setAccessible(true);
        $this->assertEquals($mappings->getValue($csvToSeeder), []);
        // mappings に値を格納
        $csvToSeeder->mappings(['name', 'view_flg',]);
        $this->assertEquals(['name', 'view_flg',], $mappings->getValue($csvToSeeder));

        // delimiter が初期化されている
        $delimiter = $reflector->getProperty('delimiter');
        $delimiter->setAccessible(true);
        $this->assertEquals($delimiter->getValue($csvToSeeder), ',');
        // delimiter に値を格納
        $csvToSeeder->delimiter(' ');
        $this->assertEquals(' ', $delimiter->getValue($csvToSeeder));

        // timestamp が初期化されている
        $timestamps = $reflector->getProperty('timestamps');
        $timestamps->setAccessible(true);
        $this->assertFalse($timestamps->getValue($csvToSeeder));
        // timestamps に値を格納
        $csvToSeeder->timestamps();
        $this->assertTrue($timestamps->getValue($csvToSeeder));

        // created_at が初期化されている
        $created_at = $reflector->getProperty('created_at');
        $created_at->setAccessible(true);
        $this->assertEquals($created_at->getValue($csvToSeeder), 'created_at');
        // created に値を格納
        $csvToSeeder->createdAt('created');
        $this->assertEquals($created_at->getValue($csvToSeeder), 'created');

        // updated_at が初期化されている
        $updated_at = $reflector->getProperty('updated_at');
        $updated_at->setAccessible(true);
        $this->assertEquals($updated_at->getValue($csvToSeeder), 'updated_at');
        // updated_at に値を格納
        $csvToSeeder->updatedAt('modified');
        $this->assertEquals($updated_at->getValue($csvToSeeder), 'modified');

        // limit が初期化されている
        $limit = $reflector->getProperty('limit');
        $limit->setAccessible(true);
        $this->assertNull($limit->getValue($csvToSeeder));
        // limit に値を格納
        $csvToSeeder->limit(2);
        $this->assertEquals($limit->getValue($csvToSeeder), 2);

        // offset が初期化されている
        $offset = $reflector->getProperty('offset');
        $offset->setAccessible(true);
        $this->assertNull($offset->getValue($csvToSeeder));
        // offset に値を格納
        $csvToSeeder->offset(2);
        $this->assertEquals($offset->getValue($csvToSeeder), 2);

        // clear() を実行
        $method->invoke($csvToSeeder);

        // 全てのプロパティが初期化されている
        $this->assertEquals($counter->getValue($csvToSeeder), 0);
        $this->assertNull($csvPath->getValue($csvToSeeder));
        $this->assertNull($model->getValue($csvToSeeder));
        $this->assertNull($instance->getValue($csvToSeeder));
        $this->assertEquals($mappings->getValue($csvToSeeder), []);
        $this->assertEquals($delimiter->getValue($csvToSeeder), ',');
        $this->assertFalse($timestamps->getValue($csvToSeeder));
        $this->assertEquals($created_at->getValue($csvToSeeder), 'created_at');
        $this->assertEquals($updated_at->getValue($csvToSeeder), 'updated_at');
        $this->assertNull($limit->getValue($csvToSeeder));
        $this->assertNull($offset->getValue($csvToSeeder));
    }

    /**
     * @test
     */
    public function insertで正しくレコードが登録される()
    {
        User::truncate();

        CsvToSeeder::init()
            ->csvPath(__DIR__ . '/../File/test.csv')
            ->header()
            ->model(User::class)
            ->mappings([
                'name',
                'view_flg',
            ])
            ->insert();

        $this->assertEquals(User::count(), 7);
    }
}
