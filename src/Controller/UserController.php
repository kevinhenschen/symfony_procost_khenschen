<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @Route("/employees", name="employees_")
 */
class UserController extends AbstractController
{
    /**
     * @Route("", name="homepage")
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param UserRepository $userRepository
     * @return Response
     */
    public function index(Request $request, PaginatorInterface $paginator, UserRepository $userRepository)
    {
        $employees = $userRepository->findAll();
        $pagination = $this->setPagination($request, $paginator, $employees);

        return $this->render('user/users.html.twig', [
            'pagination_employees' => $pagination,
        ]);
    }

    /**
     * @Route("/details/{id}", name="details")
     * @param Request $request
     * @param int $id
     * @param PaginatorInterface $paginator
     * @param UserRepository $userRepository
     * @param ProjectRepository $projectRepository
     * @return Response
     */
    public function details(
        Request $request, int $id, PaginatorInterface $paginator, UserRepository $userRepository, ProjectRepository $projectRepository)
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
                    $pagination = $this->setPagination($request, $paginator, $userProjects);

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
     * @param UserRepository $userRepository
     * @param UserPasswordEncoderInterface $userPasswordEncoder
     * @return Response
     */
    public function add(Request $request, UserRepository $userRepository, UserPasswordEncoderInterface $encoder)
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
            if ($currentUser->getId() === $id || $this->isGranted('ROLE_MANAGER')) {
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
        throw new AccessDeniedException('Vous ne pouvez pas modifier cet employée');
    }

    /**
     * @param Request $request
     * @param PaginatorInterface $paginator
     * @param array $array
     * @return PaginationInterface
     */
    private function setPagination(Request $request, PaginatorInterface $paginator, Array $array)
    {
        return $pagination = $paginator->paginate($array, $request->query->getInt('page', 1), 10);
    }
}
