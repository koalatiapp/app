<?php

namespace App\Form\Project;

use App\Entity\Project;
use App\Repository\OrganizationRepository;
use App\Security\ProjectVoter;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectSettingsType extends AbstractType
{
	public function __construct(
		private Security $security,
		private OrganizationRepository $organizationRepository,
	) {
	}

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
			]);

		// Check to add owner field
		/** @var Project */
		$project = $builder->getData();

		if ($this->security->isGranted(ProjectVoter::DELETE, $project)) {
			$builder->add('deleteConfirmation', CheckboxType::class, [
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

		if ($this->security->isGranted(ProjectVoter::CHANGE_OWNERSHIP, $project)) {
			$builder->add('owner', ChoiceType::class, [
				'mapped' => false,
				'multiple' => false,
				'expanded' => true,
				'label' => 'project_settings.project.form.field.owner.label',
				'help' => 'project_settings.project.form.field.owner.help_text',
				'choices' => $options['available_owners'],
				'data' => $project->getOwnerUser() ? 'self' : $project->getOwnerOrganization()->getId(),
				'choice_translation_parameters' => function ($choice, $key, $value) {
					if (!is_numeric($value)) {
						return [];
					}

					$organization = $this->organizationRepository->find($value);

					return [
						'%teamName%' => $organization->getName(),
					];
				},
				'choice_label' => function ($choice, $key, $value) {
					if (!is_numeric($value)) {
						return $key;
					}

					return 'project.owner.team';
				},
			]);
		}
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Project::class,
			'available_owners' => NewProjectType::getDefaultAvailableOwners(),
		]);
	}
}
