<?php

namespace App\Form\Project;

use App\Entity\Project;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectSettingsType extends AbstractType
{
	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'project_settings.project.form.field.name.label',
				'attr' => ['placeholder' => 'project_settings.project.form.field.name.placeholder', 'class' => 'medium'],
			])
			->add('url', UrlType::class, [
				'label' => 'project_settings.project.form.field.url.label',
				'attr' => ['placeholder' => 'project_settings.project.form.field.url.placeholder', 'class' => 'medium'],
			])
			->add('useCanonicalPageUrls', CheckboxType::class, [
				'label' => 'project_settings.project.form.field.use_canonical_page_urls.label',
				'help' => 'project_settings.project.form.field.use_canonical_page_urls.help_text',
				'help_html' => true,
				'required' => false,
			])
			->add('save', SubmitType::class, [
				'label' => 'project_settings.project.form.submit_label',
			])
			->add('deleteConfirmation', CheckboxType::class, [
				'label' => 'project_settings.project.delete.confirmation_label',
				'label_translation_parameters' => [
					'%name%' => $builder->getData()->getName(),
				],
				'required' => false,
				'mapped' => false,
			])
			->add('delete', SubmitType::class, [
				'label' => 'project_settings.project.delete.button_label',
				'attr' => ['color' => 'danger'],
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Project::class,
		]);
	}
}
