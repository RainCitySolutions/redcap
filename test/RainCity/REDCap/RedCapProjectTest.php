<?php
namespace RainCity\REDCap;

use IU\PHPCap\PhpCapException;
use IU\PHPCap\RedCapApiConnectionInterface;
use PHPUnit\Framework\TestCase;
use RainCity\DataCache;
use RainCity\TestHelper\ReflectionHelper;

/**
 *
 * @covers \RainCity\REDCap\RedcapProject
 *
 */
final class RedCapProjectTest extends TestCase
{
    const TEST_URL = 'https://redcap.somesite.co/redcap/api';
    const TEST_TOKEN = 'FCDEBA1234567890ABCDEF1234567890';

    /** @var RedCapProject */
    private $proj;

    /** @var RedCapApiConnectionInterface */
    private $connection;

    /**
     * {@inheritDoc}
     * @see \PHPUnit\Framework\TestCase::setUpBeforeClass()
     */
    public static function setUpBeforeClass(): void
    {
        // Ensure the cache is empty before running any of our tests
        DataCache::instance()->clear();
    }


    protected function setUp(): void
    {
        $this->connection = $this->createMock(RedCapApiConnectionInterface::class);

        $this->proj = new RedCapProject(self::TEST_URL, self::TEST_TOKEN, true, true, null, $this->connection);
    }


    public function testCtor() {
        $proj = new RedCapProject(self::TEST_URL, self::TEST_TOKEN);

        $token = ReflectionHelper::getObjectProperty(RedCapProject::class, 'apiToken', $proj, true);

        $this->assertEquals(self::TEST_TOKEN, $token);

        $conn = $proj->getConnection();
        $url = $conn->getUrl();

        $this->assertEquals(self::TEST_URL, $url);
    }

    public function testExportRedcapVersion_withException() {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 934)));

        $result = $this->proj->exportRedcapVersion();

        $this->assertNull($result);
    }

    public function testExportInstruments_exceptionPhpFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportInstruments('php');

        $this->assertEquals(array(), $result);
    }

    public function testExportInstruments_exceptionJsonFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportInstruments('json');

        $this->assertEquals('{}', $result);
    }

    public function testExportInstrumentEventMappings_exceptionPhpFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportInstrumentEventMappings();

        $this->assertEquals(array(), $result);
    }

    public function testExportInstrumentEventMappings_exceptionJsonFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportInstrumentEventMappings('json');

        $this->assertEquals('{}', $result);
    }

    public function testExportMetadata_withException()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportMetadata();

        $this->assertEquals(array(), $result);
    }

    public function testExportPdfFileOfInstruments_withException()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportPdfFileOfInstruments();

        $this->assertEquals('', $result);
    }

    public function testExportProjectInfo_exceptionPhpFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportProjectInfo('php');

        $this->assertEquals(array(), $result);
    }

    public function testExportProjectInfo_exceptionJsonFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 734)));

        $result = $this->proj->exportProjectInfo('json');

        $this->assertEquals('{}', $result);
    }

    public function testExportRecords_exceptionPhpFormat() {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 434)));

        $result = $this->proj->exportRecords('php');

        $this->assertEquals(array(), $result);
    }

    public function testExportRecords_exceptionJsonFormat() {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 434)));

        $result = $this->proj->exportRecords('json');

        $this->assertEquals('{}', $result);
    }

    public function testExportSurveyLink_withException()
    {
        $rcdId = 'Rcd987';
        $form = 'testInst';

        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 343)));

        $result = $this->proj->exportSurveyLink($rcdId, $form);

        $this->assertEquals('', $result);
    }

    public function testExportSurveyReturnCode_withException()
    {
        $recordId = 'rcdId1';
        $form = 'testInstrument';

        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 343)));

        $result = $this->proj->exportSurveyReturnCode($recordId, $form);

        $this->assertEquals('', $result);
    }

    public function testGetRecordIdFieldName_withException()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 543)));

        $result = $this->proj->getRecordIdFieldName();

        $this->assertEquals('', $result);
    }

    public function testExportEvents_exceptionPhpFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 543)));

        $result = $this->proj->exportEvents();

        $this->assertEquals(array(), $result);
    }

    public function testExportEvents_exceptionJsonFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 543)));

        $result = $this->proj->exportEvents('json');

        $this->assertEquals('{}', $result);
    }

    public function testCacheResponse() {
        $testVersion = '9.5.2';

        $this->connection->expects($this->once())->method('callWithArray')->willReturn($testVersion);

        $result = $this->proj->exportRedcapVersion();

        $this->assertEquals($testVersion, $result);

        $this->connection->expects($this->never())->method('callWithArray')->willReturn($testVersion);

        $result = $this->proj->exportRedcapVersion();

        $this->assertEquals($testVersion, $result);
    }

    public function testExportFieldNames_exceptionPhpFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 543)));

        $result = $this->proj->exportFieldNames('php');

        $this->assertEquals(array(), $result);
    }

    public function testExportFieldNames_exceptionJsonFormat()
    {
        $this->connection->method('callWithArray')->will($this->throwException(new PhpCapException('Test Exception', 543)));

        $result = $this->proj->exportFieldNames('json');

        $this->assertEquals('{}', $result);
    }
}
