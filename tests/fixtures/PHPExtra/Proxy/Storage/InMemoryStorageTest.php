<?php


/**
 * The InMemoryStorageTest class
 *
 * @author Jacek Kobus <kobus.jacek@gmail.com>
 */
class InMemoryStorageTest extends PHPUnit_Framework_TestCase {

    public function testCreateNewInstance()
    {
        return new \PHPExtra\Proxy\Storage\InMemoryStorage();
    }

    public function testResponseCanBeWrittenAndReadFromStorage()
    {
        $request = \PHPExtra\Proxy\Http\Request::create('test.html');
        $response = new \PHPExtra\Proxy\Http\Response('Works');

        $storage = new \PHPExtra\Proxy\Storage\InMemoryStorage();
        $storage->save($request, $response);

        $response = $storage->fetch($request);

        $this->assertEquals('Works', $response->getBody());
    }
}
 