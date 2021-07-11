<?php

namespace App\Form\Organization;

use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DeletionOrganizationType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('deleteConfirmation', CheckboxType::class, [
				'label' => 'organization.delete.confirmation_label',
				'label_translation_parameters' => [
					'%name%' => $builder->getData()->getName(),
				],
				'mapped' => false,
			])
			->add('delete', SubmitType::class, [
				'label' => 'organization.delete.button_label',
				'attr' => ['color' => 'danger'],
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Organization::class,
		]);
	}
}
