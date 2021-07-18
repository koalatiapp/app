<?php

namespace App\Form\Organization;

use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class LeaveOrganizationType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('leaveConfirmation', CheckboxType::class, [
				'label' => 'organization.leave.confirmation_label',
				'label_translation_parameters' => [
					'%organization%' => $builder->getData()->getName(),
				],
				'mapped' => false,
			])
			->add('delete', SubmitType::class, [
				'label' => 'organization.leave.button_label',
				'label_translation_parameters' => [
					'%organization%' => $builder->getData()->getName(),
				],
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
