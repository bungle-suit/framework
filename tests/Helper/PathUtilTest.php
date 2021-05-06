<?php

declare(strict_types=1);

namespace Bungle\Framework\Tests\Helper;

use Bungle\Framework\Helper\PathUtil;

it('has ext', function ($exp, $ext, $path) {
    expect(PathUtil::hasExt($ext, $path))->toBe($exp);
})
    ->with(
        [
            'not matched' => [false, 'txt', 'foo.jpg'],
            'not matched on file part' => [false, 'txt', 'txt'],
            'not matched on file no ext' => [false, 'txt', 'foo'],
            'matched' => [true, 'txt', 'foo.txt'],
            'matched ignore case' => [true, 'txt', 'foo.tXt'],
        ]
    );
