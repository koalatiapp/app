<?php

namespace App\Form\Organization;

use App\Entity\Organization;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewOrganizationType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'entity.organization.name',
				'attr' => ['placeholder' => 'organization.form.name_placeholder', 'class' => 'medium'],
			])
			->add('save', SubmitType::class, [
				'label' => 'organization.form.create_label',
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Organization::class,
		]);
	}
}
