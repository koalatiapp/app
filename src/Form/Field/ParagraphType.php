<?php

namespace App\Form\Field;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ParagraphType extends AbstractType
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
		return 'paragraph';
	}
}
