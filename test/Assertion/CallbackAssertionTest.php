<?php

/**
 * @see       https://github.com/laminas/laminas-permissions-acl for the canonical source repository
 * @copyright https://github.com/laminas/laminas-permissions-acl/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-permissions-acl/blob/master/LICENSE.md New BSD License
 */
namespace LaminasTest\Permissions\Acl\Assertion;

use Laminas\Permissions\Acl;

class CallbackAssertionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Ensures constructor throws InvalidArgumentException if not callable is provided
     */
    public function testConstructorThrowsExceptionIfNotCallable()
    {
        $this->setExpectedException(
            'Laminas\Permissions\Acl\Exception\InvalidArgumentException',
            'Invalid callback provided; not callable'
        );
        new Acl\Assertion\CallbackAssertion('I am not callable!');
    }

    /**
     * Ensures callback is set in object
     */
    public function testCallbackIsSet()
    {
        $callback   = function () {
        };
        $assert     = new Acl\Assertion\CallbackAssertion($callback);
        $this->assertAttributeSame($callback, 'callback', $assert);
    }

    /**
     * Ensures assert method provides callback with its arguments
     */
    public function testAssertMethodPassArgsToCallback()
    {
        $acl       = new Acl\Acl();
        $that      = $this;
        $assert    = new Acl\Assertion\CallbackAssertion(
            function ($aclArg, $roleArg, $resourceArg, $privilegeArg) use ($that, $acl) {
                $that->assertSame($acl, $aclArg);
                $that->assertInstanceOf('Laminas\Permissions\Acl\Role\RoleInterface', $roleArg);
                $that->assertEquals('guest', $roleArg->getRoleId());
                $that->assertInstanceOf('Laminas\Permissions\Acl\Resource\ResourceInterface', $resourceArg);
                $that->assertEquals('area1', $resourceArg->getResourceId());
                $that->assertEquals('somePrivilege', $privilegeArg);
                return false;
            }
        );

        $acl->addRole('guest');
        $acl->addResource('area1');
        $acl->allow(null, null, null, $assert);
        $this->assertFalse($acl->isAllowed('guest', 'area1', 'somePrivilege'));
    }

    /**
     * Ensures assert method returns callback's function value
     */
    public function testAssertMethod()
    {
        $acl       = new Acl\Acl();
        $roleGuest = new Acl\Role\GenericRole('guest');
        $assertMock = function ($value) {
            return function ($aclArg, $roleArg, $resourceArg, $privilegeArg) use ($value) {
                return $value;
            };
        };
        $acl->addRole($roleGuest);
        $acl->allow($roleGuest, null, 'somePrivilege', new Acl\Assertion\CallbackAssertion($assertMock(true)));
        $this->assertTrue($acl->isAllowed($roleGuest, null, 'somePrivilege'));
        $acl->allow($roleGuest, null, 'somePrivilege', new Acl\Assertion\CallbackAssertion($assertMock(false)));
        $this->assertFalse($acl->isAllowed($roleGuest, null, 'somePrivilege'));
    }
}
