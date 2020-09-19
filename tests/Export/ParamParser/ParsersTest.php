<?php
declare(strict_types=1);

namespace Bungle\Framework\Tests\Export\ParamParser;

use Bungle\Framework\Ent\BasalInfoService;
use Bungle\Framework\Export\DateRange;
use Bungle\Framework\Export\ParamParser\ExportContext;
use Bungle\Framework\Export\ParamParser\Parsers;
use DateTime;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

class ParsersTest extends MockeryTestCase
{
    private ExportContext $ctx;
    private Parsers $parsers;
    /** @var BasalInfoService|Mockery\MockInterface */
    private $basal;

    protected function setUp(): void
    {
        parent::setUp();

        $this->basal = Mockery::mock(BasalInfoService::class);
        $this->parsers = new Parsers($this->basal);
        $this->ctx = new ExportContext(new Request());
    }

    public function testCurrentUser(): void
    {
        $u = Mockery::mock(UserInterface::class);
        $this->basal->expects('currentUser')->andReturn($u);

        self::assertEquals([Parsers::PARAM_CURRENT_USER => $u], $this->successParse([$this->parsers, 'currentUser']));
    }

    public function testParseDateRangeInThreeMonths(): void
    {
        $this->basal->allows('today')->andReturn(new DateTime('2020-08-15'));
        $p = $this->parsers->parseDateRange('range', 'start', 'end', 92);
        $request = $this->ctx->getRequest();

        // case 1: out of range
        $request->request->set('start', '2020-01-02');
        $request->request->set('end', '2020-04-04');
        $this->failedPass('只能导出92天内的数据', $p);

        // case 2: in range
        $request->request->set('start', '2020-01-02');
        $request->query->set('end', '2020-04-02');
        self::assertEquals([
            'range' => new DateRange(new DateTime('2020-01-02'), new DateTime('2020-04-02')),
        ], $this->successParse($p));
    }

    public function testParseDateRangeWithoutThreeMonths(): void
    {
        $this->basal->allows('today')->andReturn(new DateTime('2020-08-15'));
        $p = $this->parsers->parseDateRange('range', 'start', 'end', 0);
        $request = $this->ctx->getRequest();

        $request->request->set('start', '2020-01-02');
        $request->request->set('end', '2021-05-04');
        self::assertEquals([
            'range' => new DateRange(new DateTime('2020-01-02'), new DateTime('2021-05-04')),
        ], $this->successParse($p));
    }

    public function testEnsureDateRanges(): void
    {
        $p = Parsers::ensureDateRanges(90, 'range1', 'range2', 'range3');
        $ctx = new ExportContext(new Request());

        // case 1: out of range
        $ctx->set('range1', null);
        $ctx->set('range2', new DateRange(new DateTime('2020-01-01'), new DateTime('2020-06-01')));
        $ctx->set('range3', new DateRange(null, null));
        self::assertEquals('只能导出90天内的数据', $p($ctx));

        // case 2: okay if anyone in the range.
        $ctx->set('range3', new DateRange(new DateTime('2020-08-01'), new DateTime('2020-09-01')));
        self::assertNull($p($ctx));
    }

    public function testEnsureAttrExist(): void
    {
        $p = Parsers::ensureAttrExist('foo');

        // case 1: value not exist
        $this->failedPass('Required "foo" attribute', $p);

        // case 2: value exist, but null
        $this->ctx->set('foo', null);
        self::assertEquals(['foo' => null], $this->successParse($p));

        // case 3: value exist non empty
        $this->ctx->set('foo', 'bar');
        self::assertEquals(['foo' => 'bar'], $this->successParse($p));
    }

    public function testExplode(): void
    {
        $p = Parsers::explode('foo');

        // case 1: value is not exist
        self::assertEquals(['foo' => []], $this->successParse($p));

        // case 2: value is empty
        $this->ctx->getRequest()->request->set('foo', '');
        self::assertEquals(['foo' => []], $this->successParse($p));

        // case 3: value is not empty
        $this->ctx->getRequest()->request->set('foo', 'a,b,c');
        self::assertEquals(['foo' => ['a', 'b', 'c']], $this->successParse($p));
    }

    public function testFromRequest(): void
    {
        // case 1: not exist in request, fill with default value
        $p = Parsers::fromRequest('foo', 'blah');
        self::assertEquals(['foo' => 'blah'], $this->successParse($p));

        // case 2: use default converter
        $this->ctx->getRequest()->request->set('foo', 'bar');
        self::assertEquals(['foo' => 'bar'], $this->successParse($p));

        // case 3: use converter
        $p = Parsers::fromRequest('foo', null, 'intval');
        $this->ctx->getRequest()->request->set('foo', '100.10');
        self::assertEquals(['foo' => 100], $this->successParse($p));
    }

    /**
     * @phpstan-param callable(FlowContext): mixed[]|string $parser
     */
    private function successParse(callable $parser): array
    {
        self::assertNull($parser($this->ctx));
        return $this->ctx->all();
    }

    /**
     * @phpstan-param callable(FlowContext): mixed[]|string $parser
     */
    private function failedPass(string $msg, callable $parser): void
    {
        self::assertEquals($msg, $parser($this->ctx));
    }
}
