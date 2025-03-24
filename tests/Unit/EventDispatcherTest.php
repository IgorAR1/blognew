<?php

namespace Unit;

use App\Core\Event\EventDispatcher;
use PHPUnit\Framework\TestCase;
use Psr\EventDispatcher\ListenerProviderInterface;
use Psr\EventDispatcher\StoppableEventInterface;

final class EventDispatcherTest extends TestCase
{
    public function testDispatchCallsAllListeners(): void
    {
        // Мок для ListenerProviderInterface
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // Простое событие
        $event = new \stdClass();
        $event->processed = [];

        // Два слушателя
        $listener1 = function ($e) {
            $e->processed[] = 'listener1';
        };
        $listener2 = function ($e) {
            $e->processed[] = 'listener2';
        };

        // Настраиваем мок, чтобы он возвращал массив слушателей
        $listenerProvider->method('getListenersForEvent')
            ->with($event)
            ->willReturn([$listener1, $listener2]);

        // Создаём диспетчер
        $dispatcher = new EventDispatcher($listenerProvider);

        // Выполняем диспетчеризацию
        $result = $dispatcher->dispatch($event);

        // Проверяем
        $this->assertSame($event, $result); // Возвращает тот же объект
        $this->assertEquals(['listener1', 'listener2'], $event->processed); // Все слушатели вызваны
    }

    /**
     * Тест: Остановка обработки для StoppableEventInterface
     */
    public function testDispatchStopsOnStoppableEvent(): void
    {
        // Мок для ListenerProviderInterface
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // Событие с StoppableEventInterface
        $event = $this->createMock(StoppableEventInterface::class);
        $event->processed = [];

        // Два слушателя
        $listener1 = function ($e) {
            $e->processed[] = 'listener1';
            $e->method('isPropagationStopped')->willReturn(true); // Останавливаем после первого
        };
        $listener2 = function ($e) {
            $e->processed[] = 'listener2';
        }; // Не должен вызваться

        // Настраиваем поведение мока события
        $event->method('isPropagationStopped')
            ->willReturnCallback(fn() => !empty($event->processed)); // true после первого вызова

        // Настраиваем провайдер
        $listenerProvider->method('getListenersForEvent')
            ->with($event)
            ->willReturn([$listener1, $listener2]);

        // Создаём диспетчер
        $dispatcher = new EventDispatcher($listenerProvider);

        // Диспетчеризация
        $result = $dispatcher->dispatch($event);

        // Проверяем
        $this->assertSame($event, $result); // Возвращает тот же объект
        $this->assertEquals(['listener1'], $event->processed); // Только первый слушатель вызван
        $this->assertCount(1, $event->processed); // Второй не вызван
    }

    /**
     * Тест: Исключение при не-callable слушателе
     */
    public function testDispatchThrowsExceptionForNonCallableListener(): void
    {
        // Мок для ListenerProviderInterface
        $listenerProvider = $this->createMock(ListenerProviderInterface::class);

        // Простое событие
        $event = new \stdClass();

        // Некорректный слушатель (не callable)
        $invalidListener = 'not_a_callable';

        // Настраиваем провайдер
        $listenerProvider->method('getListenersForEvent')
            ->with($event)
            ->willReturn([$invalidListener]);

        // Создаём диспетчер
        $dispatcher = new EventDispatcher($listenerProvider);

        // Ожидаем исключение
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Event listener is not a callable');

        // Диспетчеризация
        $dispatcher->dispatch($event);
    }
}