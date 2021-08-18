<?php

namespace App\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SpacerType extends AbstractType
{
	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'disabled' => true,
			'required' => false,
			'mapped' => false,
		]);
	}

	public function getBlockPrefix(): string
	{
		return 'spacer';
	}
}
