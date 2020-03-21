<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/employees", name="employees_")
 */
class UserController extends AbstractController
{

    /**
     * @var string
     */
    private $contactEmailAddress;
    /**
     * @var MailerInterface
     */
    private $mailer;

    public function __construct(string $contactEmailAddress, MailerInterface $mailer)
    {
        $this->contactEmailAddress = $contactEmailAddress;
        $this->mailer = $mailer;
    }

    /**
     * @Route("", name="homepage")
     * @param Request $request
     * @param PaginationController $paginationController
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(
        Request $request,
        PaginationController $paginationController,
        UserRepository $userRepository
    )
    {
        $employees = $userRepository->findAll();
        $pagination = $paginationController->setPagination($request, $employees);

        return $this->render('user/users.html.twig', [
            'pagination_employees' => $pagination,
        ]);
    }

    /**
     * @Route("/details/{id}", name="details")
     * @param Request $request
     * @param int $id
     * @param PaginationController $paginationController
     * @param UserRepository $userRepository
     * @param ProjectRepository $projectRepository
     * @return Response
     */
    public function details(
        Request $request,
        int $id,
        PaginationController $paginationController,
        UserRepository $userRepository,
        ProjectRepository $projectRepository)
    {

        $currentUser = $this->getUser();

        if ($currentUser instanceof User) {
            if ($currentUser->getId() === $id || $this->isGranted('ROLE_MANAGER')) {
                $employee = $userRepository->find(['id' => $id]);
                if (is_null($employee) || !($employee instanceof User)) {
                    return $this->redirectToRoute('main_dashboard');
                } else {
                    $projects = $projectRepository->findAll();

                    $userProjects = $employee->getUserProjects()->getValues();
                    $pagination = $paginationController->setPagination($request, $userProjects);

                    return $this->render('user/user_details.html.twig', [
                        'employee' => $employee,
                        'projects' => $projects,
                        'pagination_user_project' => $pagination
                    ]);
                }
            }
        }
        throw new AccessDeniedException('Vous ne pouvez pas voir cet employée');
    }

    /**
     * @Route("/add", name="add")
     * @param Request $request
     * @param UserPasswordEncoderInterface $encoder
     * @return Response
     * @throws TransportExceptionInterface
     */
    public function add(Request $request, UserPasswordEncoderInterface $encoder)
    {

        $user = (new User());
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $plainPassword = $user->generatePassword();
            $encoded = $encoder->encodePassword($user, $plainPassword);
            $user->setPassword($encoded);

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();
            $this->addFlash('success',
                $this->renderView('user/user_form_success_flash.html.twig', [
                    'password' => $plainPassword
                ])
            );

            $email = (new Email())
                ->from($this->contactEmailAddress)
                ->to($user->getEmail())
                ->subject('Bienvenue sur PROCOST ' . $user->getUsername() . '!')
                ->html($this->renderView(
                    'email/contact.html.twig',
                    ['user' => $user, 'plain_password' => $plainPassword]
                ));

            $this->mailer->send($email);

            return $this->redirectToRoute('employees_add');
        }

        return $this->render('user/user_form.html.twig', [
            "form" => $form->createView(),
            "app_title" => 'Nouvel Employée'
        ]);
    }

    /**
     * @Route("/edit/{id}", name="edit")
     * @param Request $request
     * @param int $id
     * @param UserRepository $userRepository
     * @return Response
     */
    public function edit(Request $request, int $id, UserRepository $userRepository)
    {
        $currentUser = $this->getUser();

        if ($currentUser instanceof User) {
            if ($currentUser->getId() === $id) {
                $user = $userRepository->find(['id' => $id]);
                if (!is_null($user) && $user instanceof User) {
                    $form = $this->createForm(UserType::class, $user);
                    $form->handleRequest($request);

                    if ($form->isSubmitted() && $form->isValid()) {
                        $em = $this->getDoctrine()->getManager();
                        $em->persist($user);
                        $em->flush();
                        $this->addFlash('success', 'Modification avec succées !');
                        return $this->redirectToRoute('employees_edit', [
                            'id' => $id
                        ]);
                    }

                    return $this->render('user/user_form.html.twig', [
                        "form" => $form->createView(),
                        "app_title" => 'Modification de l\'employée ' . $user->getPrenom() . " " . $user->getNom()
                    ]);

                } else {
                    return $this->redirectToRoute('main_dashboard');
                }
            }
        }
        throw new AccessDeniedException
        (
            ($this->isGranted('ROLE_MANAGER') ?
                'En tant que Manager' : 'En tant qu\'employé')
                . ', vous ne pouvez pas modifier cet employé.'
        );
    }
}
