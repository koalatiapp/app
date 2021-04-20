<?php

return [
	Symfony\Bundle\FrameworkBundle\FrameworkBundle::class => ['all' => true],
	Symfony\Bundle\TwigBundle\TwigBundle::class => ['all' => true],
	Symfony\Bundle\WebProfilerBundle\WebProfilerBundle::class => ['dev' => true, 'test' => true],
	Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
	Doctrine\Bundle\DoctrineBundle\DoctrineBundle::class => ['all' => true],
	Doctrine\Bundle\MigrationsBundle\DoctrineMigrationsBundle::class => ['all' => true],
	Symfony\Bundle\SecurityBundle\SecurityBundle::class => ['all' => true],
	Doctrine\Bundle\FixturesBundle\DoctrineFixturesBundle::class => ['dev' => true, 'test' => true],
	FOS\JsRoutingBundle\FOSJsRoutingBundle::class => ['all' => true],
	Pyrrah\GravatarBundle\PyrrahGravatarBundle::class => ['all' => true],
	DAMA\DoctrineTestBundle\DAMADoctrineTestBundle::class => ['test' => true],
	Knp\Bundle\TimeBundle\KnpTimeBundle::class => ['all' => true],
	Bazinga\Bundle\JsTranslationBundle\BazingaJsTranslationBundle::class => ['all' => true],
	Symfony\Bundle\MercureBundle\MercureBundle::class => ['all' => true],
	Twig\Extra\TwigExtraBundle\TwigExtraBundle::class => ['all' => true],
	Symfony\WebpackEncoreBundle\WebpackEncoreBundle::class => ['all' => true],
];
