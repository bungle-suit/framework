<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\StateMachine\STT;

use Bungle\Framework\StateMachine\STT\AbstractSTT;
use Bungle\Framework\StateMachine\STT\STTInterface;

/**
 * STT that all steps are empty, use the same workflow as OrderSTT
 * @extends AbstractSTT<\Bungle\Framework\Tests\StateMachine\Entity\Order>
 */
class EmptySTT extends AbstractSTT implements STTInterface
{
    protected function steps(): array
    {
        return [
           'actions' => [
               'save' => [],
               'update' => [],
               'check' => [],
           ],

            'saveActions' => [
                'saved' => [],
            ],
        ];
    }

    public static function getHigh(): string
    {
        return 'ord';
    }

}
