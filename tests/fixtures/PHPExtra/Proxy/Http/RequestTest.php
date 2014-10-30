<?php


/**
 * The RequestTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class RequestTest extends PHPUnit_Framework_TestCase 
{
    public function testHeadersCanBeAddedToRequestObject()
    {
        $request = new \PHPExtra\Proxy\Http\Request();
        $request->addHeader('Test', 123);

        $this->assertEquals(123, $request->getHeader('Test'));
        $this->assertEquals(123, $request->getHeader('test'));
    }

    public function testGivenRequestObjectIsAbleToCheckForHeaderExistence()
    {
        $request = new \PHPExtra\Proxy\Http\Request();
        $request->addHeader('Test', 123);

        $this->assertTrue($request->hasHeader('Test'));
        $this->assertTrue($request->hasHeader('test'));
    }
}
 