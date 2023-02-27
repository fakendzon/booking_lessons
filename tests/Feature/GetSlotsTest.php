<?php

namespace Tests\Feature;

use PHPHtmlParser\Dom;
use Tests\TestCase;
use Illuminate\Support\Facades\Http;
use Mockery;
use Mockery\MockInterface;

class GetSlotsTest extends TestCase
{
    //@todo провайдер и параметр get для фильтра
    public function testGetSlots(): void //@Todo rename
    {
        $fakeDates = '[{"date": "2023-02-27"}, {"date": "2023-02-28"}]';
        Http::fake([env('PLATFORM_AJAX_ADMIN') . '*' => Http::response($fakeDates)]);
        $this->instance(
            Dom::class,
            Mockery::mock(Dom::class, function (MockInterface $mock) {
                $mock
                    ->shouldReceive('loadFromUrl') //@todo ссылка на метод
                    ->withArgs([env('PLATFORM_SCHEDULE_FAVORITE_TUTOR') . '?date=2023-02-27'])
                    ->once()->andReturns((new Dom())->loadFromFile(base_path('tests/Fixtures/2023-02-27.html'))) //@todo Fixtures вытащить по пути, потому что-то придумать по названию теста и сохранению универсального пути
                    ->shouldReceive('loadFromUrl') //@todo ссылка на метод, вообще должно работать из коробки, если объявить просто mock -> работает, вожно не понадобится когда сделаю фасад
                    ->withArgs([env('PLATFORM_SCHEDULE_FAVORITE_TUTOR') . '?date=2023-02-28'])
                    ->once()->andReturns((new Dom())->loadFromFile(base_path('tests/Fixtures/2023-02-28.html'))); //@todo fixtures
            })->makePartial()
        );
        $response = $this->get('/getslots');
        $response->assertJson([
            '2023-02-27' => ['16 января 2023 в 11:00', '16 января 2023 в 12:00'], //@todo оставить только время
            '2023-02-28' => ['16 января 2023 в 15:00', '16 января 2023 в 16:00']
        ]);
        $response->assertStatus(200);
    }
}
