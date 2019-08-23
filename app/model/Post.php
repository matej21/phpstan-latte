<?php declare(strict_types = 1);

namespace App\Model;

class Post
{
	/** @var string */
	private $title;

	/** @var string */
	private $perex;

	/** @var \DateTimeImmutable */
	private $publishedAt;


	public function __construct(string $title, string $perex, \DateTimeImmutable $publishedAt)
	{
		$this->title = $title;
		$this->perex = $perex;
		$this->publishedAt = $publishedAt;
	}


	/**
	 * @return string
	 */
	public function getTitle(): string
	{
		return $this->title;
	}


	/**
	 * @return string
	 */
	public function getPerex(): string
	{
		return $this->perex;
	}


	/**
	 * @return \DateTimeImmutable
	 */
	public function getPublishedAt(): \DateTimeImmutable
	{
		return $this->publishedAt;
	}
}
