<?php

namespace App\Form;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewProjectType extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'project_creation.form.field.name.label',
				'attr' => ['placeholder' => 'project_creation.form.field.name.placeholder', 'autofocus' => 'true'],
			])
			->add('url', UrlType::class, [
				'label' => 'project_creation.form.field.url.label',
				'attr' => ['placeholder' => 'project_creation.form.field.url.placeholder'],
			])
			->add('save', SubmitType::class, [
				'label' => 'project_creation.form.submit_label',
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Project::class,
		]);
	}
}
