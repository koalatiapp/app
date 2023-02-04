<?php

namespace App\Form\Project;

use App\Entity\Project;
use App\Repository\OrganizationRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewProjectType extends AbstractType
{
	private ?OrganizationRepository $organizationRepository = null;

	#[\Symfony\Contracts\Service\Attribute\Required]
	public function setOrganizationRepository(OrganizationRepository $organizationRepository): void
	{
		$this->organizationRepository = $organizationRepository;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('name', TextType::class, [
				'label' => 'project_creation.form.field.name.label',
				'attr' => ['placeholder' => 'project_creation.form.field.name.placeholder', 'autofocus' => 'true', 'class' => 'medium'],
			])
			->add('url', UrlType::class, [
				'label' => 'project_creation.form.field.url.label',
				'attr' => ['placeholder' => 'project_creation.form.field.url.placeholder', 'class' => 'medium'],
			])
			->add('owner', ChoiceType::class, [
				'mapped' => false,
				'multiple' => false,
				'expanded' => true,
				'label' => 'project_creation.form.field.owner.label',
				'choices' => $options['available_owners'],
				'data' => $options['current_owner'],
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
			])
			->add('save', SubmitType::class, [
				'label' => 'project_creation.form.submit_label',
			]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => Project::class,
			'current_owner' => 'self',
			'available_owners' => self::getDefaultAvailableOwners(),
		]);
	}

	/**
	 * @return array<string,mixed>
	 */
	public static function getDefaultAvailableOwners(): array
	{
		return [
			'project.owner.self' => 'self',
		];
	}
}
