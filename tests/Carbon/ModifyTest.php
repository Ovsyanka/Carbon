<?php

/*
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Tests\Carbon;

use Carbon\Carbon;
use Tests\AbstractTestCase;

class ModifyTest extends AbstractTestCase
{
    public function testSimpleModify()
    {
        $a = new Carbon('2014-03-30 00:00:00');
        $b = $a->copy();
        $b->addHours(24);
        $this->assertSame(24, $a->diffInHours($b));
    }

    public function testTimezoneModify()
    {
        $a = new Carbon('2014-03-30 00:00:00', 'Europe/London');
        $b = $a->copy();
        $b->addHours(24);
        $this->assertSame(24, $a->diffInHours($b));
    }

    public function testTemporary() {
        // $date = new Carbon('2014-10-25 01:30:00', 'Europe/London'); // UTC+1
        // $date->modify('+1 day');
        // $dateExepcted = new Carbon('2014-10-26 00:30:00', 'Europe/London'); // UTC+0
        // $this->assertEquals($dateExepcted, $date);
        // $this->assertEquals($dateExepcted->getTimestamp(), $date->getTimestamp());

        // $date = new Carbon('2014-10-27 01:30:00', 'Europe/London'); // UTC+0
        // $date->modify('-1 day');
        // $dateExepcted = new Carbon('2014-10-26 01:30:00', 'Europe/London'); // UTC+0
        // $this->assertEquals($dateExepcted, $date);

        // $date = new Carbon('2014-10-27 00:00:00', 'Europe/London'); // UTC+0
        // $date->modify('-1 day');
        // $dateExepcted = new Carbon('2014-10-26 00:00:00', 'Europe/London'); // UTC+1
        // $this->assertEquals($dateExepcted, $date);

        // $date = new Carbon('2014-03-29 01:30:00', 'Europe/London'); // UTC+0
        // $date->modify('+1 day');
        // $dateExepcted = new Carbon('2014-03-30 02:30:00', 'Europe/London'); // UTC+1
        // $this->assertEquals($dateExepcted, $date);
        
        // Тут меняетяс время и это иллюстрируется вторым тестом где день отнимается/прибавляется и в итоге время
        // отличается. Надо бы так и построить тесты для разных вариантов смены ДСТ.

        // $date = new Carbon('2014-03-31 01:30:00', 'Europe/London'); // UTC+1
        // $date->modify('-1 day');
        // $dateExepcted = new Carbon('2014-03-30 00:30:00', 'Europe/London'); // UTC+0
        // $this->assertEquals($dateExepcted, $date);

        // $date = new \DateTime('2014-03-31 01:30:00', timezone_open('Europe/London')); // UTC+1
        // $date->modify('-1 day');
        // $date->modify('+1 day');
        // $dateExepcted = new \DateTime('2014-03-31 01:30:00', timezone_open('Europe/London')); // UTC+1
        // $this->assertEquals($dateExepcted, $date);
         
        $date = new \DateTime('2017-10-28 10:30:00', timezone_open('Europe/Warsaw'));
        var_dump($date->format('Y-m-d H:i:s P'));
        $date = new \DateTime('2017-10-29 10:30:00', timezone_open('Europe/Warsaw'));
        var_dump($date->format('Y-m-d H:i:s P'));
    }

    public function testDstModifications() {
        $tz = 'Europe/London';
        $date = new Carbon('2014-03-31 01:30:00', $tz);
    }

    /**
     * Tests that daylight saving hours counts in the right way to different modify arguments
     *
     * @param string $dateString date string wich should be modified
     * @param string $timezone   timezone string in wich $dateString is
     * @param string $modify     modify argument
     * @param string $expected   date, that shoud be maked after modifying $dateString in the format: 'Y-m-d H:i:s P'
     *
     * @dataProvider getDealWithDayLightTests
     */
    public function testDealWithDayLight($dateString, $timezone, $modify, $expected)
    {
        $date = new Carbon($dateString, $timezone);
        $date->modify($modify);
        $this->assertSame($expected, $date->format('Y-m-d H:i:s P'));
        $this->assertInstanceOfCarbon($date);
    }

    /**
     * Provider for testDealWithDayLight
     *
     * DayLight saving hours should be counted only if date is modifying by hours or smaller units. The dates
     *   2014-03-30/31 chosen because that is DST related time change point. When local standard time was about to reach
     *   `2014-03-30 01:00:00 +00:00` clocks were turned forward 1 hour to `2014-03-30 02:00:00 +01:00` local daylight
     *   time instead.
     */
    public function getDealWithDayLightTests()
    {
        return array(
            // TODO:
            // Написать тесты в стиле "добавил юниты", "проверил разницу".

            array('2014-03-30 00:59:59', 'Europe/London'          , '+2 sec'   , '2014-03-30 02:00:01 +01:00'),
            array('2014-03-30 00:00:00', 'Europe/London', '+3600 sec'   , '2014-03-30 02:00:00 +01:00'),
            array('2014-03-30 00:00:00', 'Europe/London', '+7199 sec'   , '2014-03-30 02:59:59 +01:00'),

            // Тест ниже падает. То есть если изменение даты идет до времени на которое переводятся часы - все ок.
            // А если после - тогда он применяет корректировку времени, которая уместна в случае дней.

            array('2014-03-30 00:00:00', 'Europe/London', '+7200 sec'   , '2014-03-30 03:00:00 +01:00'),
            // array('2014-03-30 00:00:00', 'Europe/London', '+86400 sec'   , '2014-03-31 01:00:00 +01:00'),
            // // some simple checks
            // array('2014-03-30 00:00:00', 'UTC'          , '+86400 sec'   , '2014-03-31 00:00:00 +00:00'),
            // array('2014-03-30 00:00:00', 'Europe/London', '+86400 sec'   , '2014-03-31 01:00:00 +01:00'),
            // array('2014-03-30 00:00:00', 'UTC'          , '+1400 minutes', '2014-03-30 23:20:00 +00:00'),
            // array('2014-03-30 00:00:00', 'Europe/London', '+1400 minutes', '2014-03-31 00:20:00 +01:00'),
            // array('2014-03-30 00:00:00', 'UTC'          , '+24 hours'    , '2014-03-31 00:00:00 +00:00'),
            // array('2014-03-30 00:00:00', 'Europe/London', '+24 hours'    , '2014-03-31 01:00:00 +01:00'),
            // array('2014-03-30 00:00:00', 'UTC'          , '+1 day'       , '2014-03-31 00:00:00 +00:00'),
            // array('2014-03-30 00:00:00', 'Europe/London', '+1 day'       , '2014-03-31 00:00:00 +01:00'),
            // array('2014-03-31 10:00:00', 'UTC'          , 'midnight'     , '2014-03-31 00:00:00 +00:00'),
            // array('2014-03-31 10:00:00', 'Europe/London', 'midnight'     , '2014-03-31 00:00:00 +01:00'),

            // // should work with any symbols case
            // array('2014-03-30 00:00:00', 'Europe/London', '+86400 Secs', '2014-03-31 01:00:00 +01:00'),
            
            // // `second` can be a `time unit` or `ordinal unit`, so I have to test that `ordinal unit` works without
            // // changes and `time unit` fixed.
            
            // // `ordinal space unit` form acts relatively like numeric one: "first" mean "+1", "second" mean "+2", "last"
            // // mean "-1"
            
            // // `second second` means `+2 seconds`
            // array('2014-03-30 00:59:59', 'Europe/London', 'second sec', '2014-03-30 02:00:01 +01:00'),
            // array('2014-03-30 00:59:59', 'Europe/London', 'second second', '2014-03-30 02:00:01 +01:00'),
            // array('2014-03-30 00:59:59', 'Europe/London', '2 second', '2014-03-30 02:00:01 +01:00'),
            // array('2014-03-30 00:59:59', 'Europe/London', '+2 second', '2014-03-30 02:00:01 +01:00'),
            // array('2014-03-30 00:59:59', 'Europe/London', 'second minute', '2014-03-30 02:01:59 +01:00'),
            // array('2014-03-30 00:59:59', 'Europe/London', 'second hour', '2014-03-30 02:59:59 +01:00'),

            // // And negative changes in few different ways. Here only hours tests. Seconds related tests I put below
            
            // array('2014-03-30 02:00:01', 'Europe/London', 'second hour ago', '2014-03-30 00:00:01 +00:00'),

            // // `second day` means `+2 days`. So it should work same
            // array('2014-03-29 01:30:00', 'Europe/London', 'second day', '2014-03-31 01:30:00 +01:00'),
            // array('2014-03-29 01:30:00', 'Europe/London', '+2 day', '2014-03-31 01:30:00 +01:00'),
            
            // // And in opposite direction, just for sure. This case works before fix and should still work after.
            // array('2014-03-31 01:30:00', 'Europe/London', 'second day ago', '2014-03-29 01:30:00 +00:00'),
            // array('2014-03-31 01:30:00', 'Europe/London', '-2 day', '2014-03-29 01:30:00 +00:00'),
            
            // // In this case I trying to set time that doesn't exists. Because after 00:59 there is 2:00. So php adds
            // // one hour to all time between 1 and 2 hours. IMHO, it would be better to handle it like 1:30 in UTC+1
            // // means 0:30 in UTC+0. And 0:30 is a correct (existed) time. But it works in that way and here I just
            // // testing this.
            // array('2014-03-31 01:30:00', 'Europe/London', 'last day', '2014-03-30 02:30:00 +01:00'),
            // array('2014-03-31 01:30:00', 'Europe/London', '-1 day', '2014-03-30 02:30:00 +01:00'),

            // array('2014-03-30 02:00:01', 'Europe/London', 'second sec ago', '2014-03-30 02:59:59 +01:00'),
            // array('2014-03-30 02:00:01', 'Europe/London', 'second second ago', '2014-03-30 02:59:59 +01:00'),
            // array('2014-03-30 02:00:01', 'Europe/London', '2 second ago', '2014-03-30 02:59:59 +01:00'),
            // array('2014-03-30 02:00:01', 'Europe/London', '-2 second', '2014-03-30 02:59:59 +01:00'),

            // // Checks that time didn't changed on other (not `second`) relative data changes
            // // The month still March because 31 of Feb is 03 of March.
            // array('2014-03-31 00:59:59', 'Europe/London', 'last month', '2014-03-03 00:59:59 +00:00'),
            // // `last day of` is a special form, it is not just `last day`. Just for sure testing it too.
            // array('2014-03-30 00:59:59', 'Europe/London', 'last day of March 2014', '2014-03-31 00:59:59 +01:00'),
        );
    }
}
