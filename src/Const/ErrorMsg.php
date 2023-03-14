<?php

namespace App\Libraries\Consts;

/**
 * @author k.nagama <k.nagama0632@gmail.com>
 */
final class ErrorMsg
{
    /**
     * @var string
     */
    public const VALIDATE_NOT_MODEL = 'モデルが指定されていません。';

    /**
     * @var string
     */
    public const VALIDATE_NOT_MAPPINGS = 'マッピングがされていません。';

    /**
     * @var string
     */
    public const VALIDATE_ERROR = 'パラメータが不正です。';

    /**
     * @var string
     */
    public const FILE_NOT_FOUND = 'CSVのパスが正しくありません。';
}
