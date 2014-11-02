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

        $value = $request->getHeader('Test');

        $this->assertTrue($request->hasHeaderWithValue('Test', 123));
        $this->assertEquals(123, $value[0]);
    }

    public function testGivenRequestObjectIsAbleToCheckForHeaderExistence()
    {
        $request = new \PHPExtra\Proxy\Http\Request();
        $request->addHeader('Test', 123);

        $this->assertTrue($request->hasHeader('test'));
    }
}
 