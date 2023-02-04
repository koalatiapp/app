<?php

namespace App\Form\User;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPassword;
use Symfony\Component\Validator\Constraints\NotBlank;

class UserDeleteAccountType extends AbstractType
{
	private readonly User $user;

	public function __construct(TokenStorageInterface $tokenStorage)
	{
		/** @var User */
		$currentUser = $tokenStorage->getToken()->getUser();
		$this->user = $currentUser;
	}

	/**
	 * @SuppressWarnings(PHPMD.UnusedFormalParameter.options)
	 */
	public function buildForm(FormBuilderInterface $builder, array $options): void
	{
		$builder
			->add('currentPassword', PasswordType::class, [
				'label' => 'user_settings.security.delete_account.form.current_password',
				'attr' => ['class' => 'medium'],
				'mapped' => false,
				'constraints' => [
					new UserPassword([
						'message' => 'The password you entered did not match your current password.',
					]),
				],
			])
			->add('deleteConfirmation', CheckboxType::class, [
				'label' => 'user_settings.security.delete_account.form.confirmation_label',
				'mapped' => false,
				'constraints' => [new NotBlank()],
			]);

		if ($this->user->getOwnedOrganization()) {
			$builder->add('deleteTeamConfirmation', CheckboxType::class, [
				'label' => 'user_settings.security.delete_account.form.team_deletion_confirmation_label',
				'label_translation_parameters' => ['%teamName%' => $this->user->getOwnedOrganization()],
				'mapped' => false,
				'constraints' => [new NotBlank()],
			]);
		}

		$builder->add('delete', SubmitType::class, [
			'label' => 'user_settings.security.delete_account.form.submit_label',
			'attr' => ['color' => 'danger'],
		]);
	}

	public function configureOptions(OptionsResolver $resolver): void
	{
		$resolver->setDefaults([
			'data_class' => User::class,
		]);
	}
}
