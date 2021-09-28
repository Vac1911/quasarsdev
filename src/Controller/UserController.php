<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    #[Route('/user', name: 'user.index', methods: ['GET'])]
    public function index(): Response
    {
        $users = $this->getDoctrine()
            ->getRepository(User::class)
            ->findAll();

        return $this->render('user/index.twig', [
            'users' => $users,
        ]);
    }

    #[Route('/user/new', name: 'user.create', methods: ['GET'])]
    public function create(Request $request): Response
    {
        $user = new User();

        return $this->render('user/create.twig', [
            'user' => $user,
        ]);
    }
    #[Route('/user/new', name: 'user.store', methods: ['POST'])]
    public function store(Request $request): Response
    {
        $user = new User();
        $user->fill($request->request->all());
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('user.show', ['user' => $user->id]);
    }



    #[Route('/user/{user}', name: 'user.show', methods: ['GET'])]
    public function show(User $user): Response
    {
        return $this->render('user/show.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{user}/edit', name: 'user.edit', methods: ['GET'])]
    public function edit(User $user): Response
    {
        return $this->render('user/edit.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/user/{user}/edit', name: 'user.update', methods: ['POST'])]
    public function update(Request $request, User $user): Response
    {
        $user->fill($request->request->all());
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->redirectToRoute('user.show', ['user' => $user->id]);
    }

    /**
     * @Route("/{id}", name="user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();
        }

        return $this->redirectToRoute('user.index', [], Response::HTTP_SEE_OTHER);
    }
}
