<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Meet;
use App\Form\MeetType;
use Doctrine\ORM\NonUniqueResultException;
use Swift_Message;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/meet')]
class MeetController extends AbstractBaseController
{
    /**
     * Lists all Meet entities.
     *
     * @Route("", name="app_meet_index", methods={"GET"})
     *
     * @throws NonUniqueResultException
     */
    public function index(int $page = 1, string $search = ''): Response
    {
        $manager = $this->getDoctrine()->getManager();

        // Escape special characters and decode the search value.
        $search = addcslashes(urldecode($search), '%_');

        // Get the total entries.
        $count = $manager
            ->getRepository(Meet::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.title LIKE :title')
            ->setParameter(':title', '%' . $search . '%')
            ->orWhere('e.subject LIKE :subject')
            ->setParameter(':subject', '%' . $search . '%')
            ->orWhere('e.text LIKE :text')
            ->setParameter(':text', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();

        // Define the number of pages.
        $pages = ceil($count / 20);

        // Get the entries of current page.
        /** @var Meet[] $meetList */
        $meetList = $manager
            ->getRepository(Meet::class)
            ->createQueryBuilder('e')
            ->where('e.title LIKE :title')
            ->setParameter(':title', '%' . $search . '%')
            ->orWhere('e.subject LIKE :subject')
            ->setParameter(':subject', '%' . $search . '%')
            ->orWhere('e.text LIKE :text')
            ->setParameter(':text', '%' . $search . '%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('meet/index.html.twig', [
            'meetList' => $meetList,
            'pages' => $pages,
            'page' => $page,
            'search' => stripslashes($search),
            'searchForm' => $this->createSearchForm(stripslashes($search))->createView(),
        ]);
    }

    /**
     * Creates a form to search Meet entities.
     *
     * @return FormInterface The form
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_meet_search'))
            ->setMethod('post')
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * Creates a new Meet entity.
     *
     * @Route("/create", name="app_meet_create", methods={"POST"})
     */
    public function create(Request $request): Response
    {
        $meet = new Meet();
        $form = $this->createCreateForm($meet);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $meet->setAuthor($this->getUser());
            $meet->setPublisher($this->getUser());
            $meet->setSchool($this->getEntitySchool());

            $manager->persist($meet);
            $manager->flush();

            $this->addFlash('success', 'The Meet has been created.');

            $this->sendMail($meet);

            return $this->redirect($this->generateUrl('app_meet_show', ['id' => $meet->getId()]));
        }

        return $this->render('meet/new.html.twig', [
            'meet' => $meet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Creates a form to create a Meet entity.
     *
     * @param Meet $meet The entity
     *
     * @return FormInterface The form
     */
    private function createCreateForm(Meet $meet)
    {
        $form = $this->createForm(MeetType::class, $meet, [
            'action' => $this->generateUrl('app_meet_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * sendMail.
     */
    private function sendMail(Meet $meet): bool
    {
        $message = Swift_Message::newInstance()
            ->setSubject($meet->getTitle())
            ->setTo('h.fahari@gmail.com')
            ->setBody($meet->getSubject());

        $this->get('mailer')->send($message);

        return true;
    }

    /**
     * Displays a form to create a new Meet entity.
     *
     * @Route("/new", name="app_meet_new", methods={"GET"})
     */
    public function new(): Response
    {
        $meet = new Meet();
        $form = $this->createCreateForm($meet);

        return $this->render('meet/new.html.twig', [
            'meet' => $meet,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Meet entity.
     *
     * @Route("/show/{id}", name="app_meet_show", methods={"GET"})
     */
    public function show(Meet $meet): Response
    {
        return $this->render('meet/show.html.twig', [
            'meet' => $meet,
        ]);
    }

    /**
     * Displays a form to edit an existing Meet entity.
     *
     * @Route("/edit/{id}", name="app_meet_edit", methods={"GET"})
     */
    public function edit(Meet $meet): Response
    {
        $editForm = $this->createEditForm($meet);

        return $this->render('meet/edit.html.twig', [
            'meet' => $meet,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a Meet entity.
     *
     * @param Meet $meet The entity
     *
     * @return FormInterface The form
     */
    private function createEditForm(Meet $meet): FormInterface
    {
        $form = $this->createForm(MeetType::class, $meet, [
            'action' => $this->generateUrl('app_meet_update', ['id' => $meet->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    /**
     * Edits an existing Meet entity.
     *
     * @Route("/update/{id}", name="app_meet_update", methods={"PUT", "POST"})
     *
     * @return RedirectResponse|Response
     */
    public function update(Request $request, Meet $meet): Response
    {
        $editForm = $this->createEditForm($meet);
        $editForm->handleRequest($request);

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

            $this->addFlash('success', 'The Meet has been updated.');

            return $this->redirect($this->generateUrl('app_meet_show', ['id' => $meet->getId()]));
        }

        return $this->render('meet/edit.html.twig', [
            'meet' => $meet,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Meet entity.
     *
     * @Route("/delete/{id}", name="app_meet_delete", methods={"GET", "DELETE"})
     *
     * @return RedirectResponse|Response
     */
    public function delete(Request $request, Meet $meet)
    {
        $deleteForm = $this->createDeleteForm($meet->getId());
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $manager = $this->getManager();
            $manager->remove($meet);
            $manager->flush();

            $this->addFlash('success', 'The Meet has been deleted.');

            return $this->redirect($this->generateUrl('app_meet_index'));
        }

        return $this->render('meet/delete.html.twig', [
            'meet' => $meet,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a Meet entity by id.
     *
     * @param mixed $id The entity id
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_meet_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     *
     * @Route("/search", name="app_meet_search", methods={"GET"})
     *
     * @return RedirectResponse
     */
    public function search(Request $request)
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_meet_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }
}
