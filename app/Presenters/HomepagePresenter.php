<?php

declare(strict_types=1);

namespace App\Presenters;

use App\Model\Post;
use Nette;

/**
 * @property HomepageDefaultView $template
 */
final class HomepagePresenter extends Nette\Application\UI\Presenter
{
	public function renderDefault()
	{
		$this->template->posts = [
			new Post('Hello world', 'Lorem ipsum', new \DateTimeImmutable('2019-08-20 10:00')),
			new Post('Ahoj svelte', 'Dolor sit amet', new \DateTimeImmutable('2019-08-20 12:00')),
		];
	}
}
