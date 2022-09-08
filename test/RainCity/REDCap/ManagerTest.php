<?php
namespace RainCity\REDCap;

use RainCity\DataCache;
use RainCity\TestHelper\ReflectionHelper;


/**
 *
 * @covers \RainCity\REDCap\Manager
 *
 */
class ManagerTest extends REDCapTestCase
{
/*
    public function testCtor_redcapBad() {
        $this->setCallback('exportRedcapVersion', function () {
            return null;
        });

        $this->expectException("Exception");
        new Manager($this->stubRedcapProj);
    }
*/
    public function testCtor_normal() {
        $mgr = new Manager($this->stubRedcapProj);

        $redcapProj = ReflectionHelper::getObjectProperty(Manager::class, 'redcapProject', $mgr);

        $this->assertEquals($this->stubRedcapProj, $redcapProj);
        $this->assertNull(ReflectionHelper::getObjectProperty(Manager::class, 'cache', $mgr));
        $this->assertNotnull(ReflectionHelper::getObjectProperty(Manager::class, 'cacheKey', $mgr));
    }

    public function testCtor_withCache() {
        $mockCache = $this->createMock(DataCache::class);

        $mgr = new Manager($this->stubRedcapProj, $mockCache);
        $cache = ReflectionHelper::getObjectProperty(Manager::class, 'cache', $mgr);

        $this->assertEquals($mockCache, $cache);
    }

    public function testGetProject_noProject() {
        $this->setCallback('exportProjectInfo', function() { return array(); } );
        $this->setCallback('exportInstrumentEventMappings', function() {return null;});

        $mgr = new Manager($this->stubRedcapProj);
        $project = $mgr->getProject();

        $this->assertNull($project);
    }

    public function testGetProject_cacheHit() {
        $testProject = new Project(ProjectTest::getTestProject());

        $mockCache = $this->createMock(DataCache::class);
        $mockCache->expects($this->once())->method('get')->willReturn($testProject);
        $mockCache->expects($this->never())->method('set');

        $mgr = new Manager($this->stubRedcapProj, $mockCache);
        $mgr->getProject();
    }

    public function testGetProject_cacheMiss() {
        $this->setCallback('exportInstruments', function () { return array(); } );

        $mockCache = $this->createMock(DataCache::class);
        $mockCache->expects($this->once())->method('get')->willReturn(null);
        $mockCache->expects($this->once())->method('set');

        $mgr = new Manager($this->stubRedcapProj, $mockCache);
        $mgr->getProject();
    }

    public function testGetProject_withEvents() {
        $this->setCallback('exportInstruments', function () { return array(); } );

        $mgr = new Manager($this->stubRedcapProj);
        $project = $mgr->getProject();

        $this->assertEquals(3, count($project->getEventNames()) );
    }

    public function testGetProject_withInstruments() {
        $mgr = new Manager($this->stubRedcapProj);
        $project = $mgr->getProject();

        $this->assertCount(count(static::TEST_INSTRUMENTS), $project->getInstrumentNames() );
    }

    public function testGetInstruments() {
        $mgr = new Manager($this->stubRedcapProj);
        $instruments = $mgr->getInstruments();

        $this->assertIsArray($instruments);
        $this->assertCount(count(static::TEST_INSTRUMENTS), $instruments);
    }

    public function testGetEvents() {
        $mgr = new Manager($this->stubRedcapProj);
        $events = $mgr->getEvents();

        $this->assertIsArray($events);
        $this->assertCount(count(static::TEST_EVENTS), $events);
    }

/*
    public function testCtor_noInstrument() {
        $this->stubRedcapProj->method('exportProjectInfo')->willReturn(ProjectTest::getTestProject());
        $this->stubRedcapProj->method('exportInstruments')->willReturn(array());
        $this->stubRedcapProj->method('exportMetadata')->willReturn(array());
        $this->stubRedcapProj->method('exportEvents')->willReturn(array());
        $this->stubRedcapProj->method('exportInstrumentEventMappings')->willReturn(array());

        $mgr = new Manager($this->stubRedcapProj);
        $project = $mgr->getProject();

        $this->assertSame($project->getInstruments(), $mgr->getInstruments());
    }

    public function testCtor_noMetadata() {
        $this->stubRedcapProj->method('exportProjectInfo')->willReturn(ProjectTest::getTestProject());
        $this->stubRedcapProj->method('exportInstruments')->willReturn($this->getTestInstruments());
        $this->stubRedcapProj->method('exportMetadata')->willReturn(array());
        $this->stubRedcapProj->method('exportEvents')->willReturn(array());
        $this->stubRedcapProj->method('exportInstrumentEventMappings')->willReturn(array());

        $mgr = new Manager($this->stubRedcapProj);
        $project = $mgr->getProject();

        $this->assertSame($project->getInstruments(), $mgr->getInstruments());
    }
*/
/*
    public function getProject(): Project {
        $methodLogger = new MethodLogger();

        $projectCacheKey = 'RedcapProject-'.$this->cacheKey;

        $project = isset($this->cache) ? $this->cache->get($projectCacheKey) : null;
        if (!isset($project)) {
            $project = $this->loadProject();

            if (isset($this->cache) && isset($project)) {
                $this->cache->set($projectCacheKey, $project);
            }
        }

        return $project;
    }

    public function getInstruments(): array {
        $project = $this->getProject();

        return $project->getInstruments();
    }

    public function getEvents(): array {
        $project = $this->getProject();

        return $project->getEvents();
    }

    private function loadProject(): ?Project {
        $methodLogger = new MethodLogger();
        $project = null;

        $projInfo = $this->redcapProject->exportProjectInfo();

        if (count($projInfo) != 0) {
            $project = new Project($projInfo);

            $this->loadInstruments($project);
            $this->loadEvents($project);
        }

        return $project;
    }


    private function loadInstruments(Project $project) {
        $methodLogger = new MethodLogger();
        $scopeTimer = new ScopeTimer($this->logger, 'Time to export REDCap instruments: %s');

        $instruments = $this->redcapProject->exportInstruments();
        foreach ($instruments as $name => $label) {
            $project->addInstrument(new Instrument($name, $label));
        }
        unset($instruments);

        $scopeTimer = new ScopeTimer($this->logger, 'Time to export REDCap metadata: %s');

        $metadata = $this->redcapProject->exportMetadata();
        foreach($metadata as $field) {
            $instrument = $project->getInstrument($field['form_name']);
            if (isset($instrument)) {
                $instrument->addField(new Field($field));
            }
        }
        unset($metadata);

        $scopeTimer = new ScopeTimer($this->logger, 'Time to export REDCap event mapping: %s');

        $eventdata = $this->redcapProject->exportInstrumentEventMappings();
        foreach($eventdata as $entry) {
            $instrument = $project->getInstrument($entry['form']);
            if (isset($instrument)) {
                $instrument->addEvent($entry['unique_event_name']);
            }
        }
        unset($eventdata);
    }


    private function loadEvents(Project $project) {
        $methodLogger = new MethodLogger();

        $scopeTimer = new ScopeTimer($this->logger, 'Time to export REDCap events: %s');

        $events = $this->redcapProject->exportEvents();
        foreach ($events as $event) {
            $project->addEvent(new Event ($event));
        }
        unset($events);
    }
*/
}
