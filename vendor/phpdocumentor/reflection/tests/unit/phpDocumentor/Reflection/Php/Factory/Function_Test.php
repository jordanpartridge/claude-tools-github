<?php

declare(strict_types=1);

/**
 * This file is part of phpDocumentor.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @link http://phpdoc.org
 */

namespace phpDocumentor\Reflection\Php\Factory;

use phpDocumentor\Reflection\DocBlock as DocBlockDescriptor;
use phpDocumentor\Reflection\DocBlockFactoryInterface;
use phpDocumentor\Reflection\Fqsen;
use phpDocumentor\Reflection\Php\File;
use phpDocumentor\Reflection\Php\Function_ as FunctionDescriptor;
use phpDocumentor\Reflection\Php\ProjectFactoryStrategy;
use phpDocumentor\Reflection\Php\StrategyContainer;
use PhpParser\Comment\Doc;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\Expression;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\UsesClass;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Prophecy\Prophecy\ObjectProphecy;
use stdClass;

use function current;

#[CoversClass(Function_::class)]
#[CoversClass(AbstractFactory::class)]
#[UsesClass('\phpDocumentor\Reflection\Php\Function_')]
#[UsesClass('\phpDocumentor\Reflection\Php\Argument')]
#[UsesClass('\phpDocumentor\Reflection\Php\Factory\Type')]
final class Function_Test extends TestCase
{
    use ProphecyTrait;

    private ObjectProphecy $docBlockFactory;

    protected function setUp(): void
    {
        $this->docBlockFactory = $this->prophesize(DocBlockFactoryInterface::class);
        $this->fixture = new Function_($this->docBlockFactory->reveal());
    }

    public function testMatches(): void
    {
        $this->assertFalse($this->fixture->matches(self::createContext(null), new stdClass()));
        $this->assertTrue($this->fixture->matches(
            self::createContext(null)->push(new File('hash', 'path')),
            $this->prophesize(\PhpParser\Node\Stmt\Function_::class)->reveal(),
        ));
    }

    public function testCreateWithoutParameters(): void
    {
        $functionMock = $this->prophesize(\PhpParser\Node\Stmt\Function_::class);
        $functionMock->byRef = false;
        $functionMock->stmts = [];
        $functionMock->getAttribute('fqsen')->willReturn(new Fqsen('\SomeSpace::function()'));
        $functionMock->params = [];
        $functionMock->getDocComment()->willReturn(null);
        $functionMock->getLine()->willReturn(1);
        $functionMock->getEndLine()->willReturn(2);
        $functionMock->getReturnType()->willReturn(null);

        $containerMock = $this->prophesize(StrategyContainer::class);
        $file = new File('hash', 'path');

        $this->fixture->create(self::createContext()->push($file), $functionMock->reveal(), $containerMock->reveal());

        $function = current($file->getFunctions());
        $this->assertInstanceOf(FunctionDescriptor::class, $function);
        $this->assertEquals('\SomeSpace::function()', (string) $function->getFqsen());
    }

    public function testCreateWithDocBlock(): void
    {
        $doc = new Doc('Text');
        $functionMock = $this->prophesize(\PhpParser\Node\Stmt\Function_::class);
        $functionMock->byRef = false;
        $functionMock->stmts = [];
        $functionMock->getAttribute('fqsen')->willReturn(new Fqsen('\SomeSpace::function()'));
        $functionMock->params = [];
        $functionMock->getDocComment()->willReturn($doc);
        $functionMock->getLine()->willReturn(1);
        $functionMock->getEndLine()->willReturn(2);
        $functionMock->getReturnType()->willReturn(null);

        $docBlock = new DocBlockDescriptor('');
        $this->docBlockFactory->create('Text', null)->willReturn($docBlock);

        $containerMock = $this->prophesize(StrategyContainer::class);
        $file = new File('hash', 'path');
        $this->fixture->create(self::createContext()->push($file), $functionMock->reveal(), $containerMock->reveal());

        $function = current($file->getFunctions());

        $this->assertEquals('\SomeSpace::function()', (string) $function->getFqsen());
        $this->assertSame($docBlock, $function->getDocBlock());
    }

    public function testIteratesStatements(): void
    {
        $doc = new Doc('Text');
        $functionMock = $this->prophesize(\PhpParser\Node\Stmt\Function_::class);
        $functionMock->byRef = false;
        $functionMock->stmts = [];
        $functionMock->getAttribute('fqsen')->willReturn(new Fqsen('\SomeSpace::function()'));
        $functionMock->params = [];
        $functionMock->getDocComment()->willReturn(null);
        $functionMock->getLine()->willReturn(1);
        $functionMock->getEndLine()->willReturn(2);
        $functionMock->getReturnType()->willReturn(null);
        $functionMock->stmts = [new Expression(new FuncCall(new Name('hook')))];

        $strategyMock = $this->prophesize(ProjectFactoryStrategy::class);

        $containerMock = $this->prophesize(StrategyContainer::class);
        $containerMock->findMatching(
            Argument::type(ContextStack::class),
            Argument::type(Expression::class),
        )->willReturn($strategyMock->reveal())->shouldBeCalledOnce();

        $file = new File('hash', 'path');
        $this->fixture->create(
            self::createContext(null)->push($file),
            $functionMock->reveal(),
            $containerMock->reveal(),
        );
    }
}
