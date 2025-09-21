<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\Type\DatePickerType;
use Symfony\Component\Form\Test\TypeTestCase;

/**
 * @internal
 *
 * @coversNothing
 */
final class DatePickerTypeTest extends TypeTestCase
{
    public function testDatePickerTypeUsesHtml5(): void
    {
        $form = $this->factory->create(DatePickerType::class);

        $view = $form->createView();

        // Let's check what we have in view vars
        self::assertArrayHasKey('type', $view->vars);
        self::assertSame('date', $view->vars['type'], 'Input type should be date for HTML5');
        self::assertArrayHasKey('widget', $view->vars);
        self::assertSame('single_text', $view->vars['widget']);
    }

    public function testDatePickerTypeWithData(): void
    {
        $testDate = new \DateTime('2023-12-25');

        $form = $this->factory->create(DatePickerType::class);
        $form->submit('2023-12-25');

        self::assertTrue($form->isSynchronized());
        self::assertSame($testDate->format('Y-m-d'), $form->getData()->format('Y-m-d'));
    }
}
