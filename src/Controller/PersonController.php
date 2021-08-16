<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Base\AbstractBaseController;
use App\Entity\Family;
use App\Entity\Member;
use App\Entity\Person;
use App\Entity\Student;
use App\Entity\Teacher;
use App\Form\PersonType;
use App\Manager\PhoneManager;
use App\Repository\FamilyRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Person controller.
 *
 * @Route("/person")
 */
class PersonController extends AbstractBaseController
{
    /**
     * Lists all Person entities.
     *
     * @Route("/list/{page}/{search}", name="app_person_index", methods={"GET"})
     *
     *
     * @throws NonUniqueResultException
     */
    public function index(int $page = 1, string $search = ''): Response
    {
        $em = $this->getDoctrine()->getManager();

        $count = $em
            ->getRepository(Person::class)
            ->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->where('e.name LIKE :name')
            ->setParameter(':name', '%' . $search . '%')
            ->orWhere('e.forname LIKE :forname')
            ->setParameter(':forname', '%' . $search . '%')
            ->orWhere('e.phone LIKE :phone')
            ->setParameter(':phone', '%' . $search . '%')
            ->orWhere('e.email LIKE :email')
            ->setParameter(':email', '%' . $search . '%')
            ->orWhere('e.birthplace LIKE :birthplace')
            ->setParameter(':birthplace', '%' . $search . '%')
            ->orWhere('e.gender LIKE :gender')
            ->setParameter(':gender', '%' . $search . '%')
            ->orWhere('e.address LIKE :address')
            ->setParameter(':address', '%' . $search . '%')
            ->orWhere('e.zip LIKE :zip')
            ->setParameter(':zip', '%' . $search . '%')
            ->orWhere('e.city LIKE :city')
            ->setParameter(':city', '%' . $search . '%')
            ->getQuery()
            ->getSingleScalarResult();

        $pages = ceil($count / 20);

        /** @var Person[] $personList */
        $personList = $em
            ->getRepository(Person::class)
            ->createQueryBuilder('e')
            ->where('e.name LIKE :name')
            ->setParameter(':name', '%' . $search . '%')
            ->orWhere('e.forname LIKE :forname')
            ->setParameter(':forname', '%' . $search . '%')
            ->orWhere('e.phone LIKE :phone')
            ->setParameter(':phone', '%' . $search . '%')
            ->orWhere('e.email LIKE :email')
            ->setParameter(':email', '%' . $search . '%')
            ->orWhere('e.birthplace LIKE :birthplace')
            ->setParameter(':birthplace', '%' . $search . '%')
            ->orWhere('e.gender LIKE :gender')
            ->setParameter(':gender', '%' . $search . '%')
            ->orWhere('e.address LIKE :address')
            ->setParameter(':address', '%' . $search . '%')
            ->orWhere('e.zip LIKE :zip')
            ->setParameter(':zip', '%' . $search . '%')
            ->orWhere('e.city LIKE :city')
            ->setParameter(':city', '%' . $search . '%')
            ->setFirstResult(($page - 1) * 20)
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return $this->render('person/index.html.twig', [
            'personList' => $personList,
            'pages' => $pages,
            'page' => $page,
            'search' => $search,
            'searchForm' => $this->createSearchForm($search)->createView(),
        ]);
    }

    /**
     * Creates a form to search Person entities.
     *
     *
     */
    private function createSearchForm(string $q = ''): FormInterface
    {
        $data = ['q' => $q];

        return $this->createFormBuilder($data)
            ->setAction($this->generateUrl('app_person_search'))
            ->setMethod(Request::METHOD_POST)
            ->add('q', TextType::class, [
                'label' => false,
            ])
            ->add('submit', SubmitType::class, ['label' => 'Search'])
            ->getForm();
    }

    /**
     * Creates a new Person entity.
     *
     * @Route("/create", name="app_person_create", methods={"POST"})
     */
    public function create(Request $request): RedirectResponse|Response
    {
        // If form have redirect
        $pathRedirect = $request->get('pathRedirect');

        $person = new Person();
        $form = $this->createCreateForm($person, $pathRedirect);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $person->setAuthor($this->getUser());
            $person->addSchool($this->getEntitySchool());

            $manager->persist($person);
            $manager->flush();

            $this->addFlash('success', 'The Person ' . $person->getNameComplete() . ' has been created.');

            $pathRedirect = $form->get('pathRedirect')->getData();
            $parameters = ['person' => $person->getId()];

            if (empty($pathRedirect)) {
                $pathRedirect = 'app_person_show';
                $parameters = ['id' => $person->getId()];
            }

            return $this->redirect($this->generateUrl($pathRedirect, $parameters));
        }

        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    private function createCreateForm(Person $person, string $pathRedirect = null): FormInterface
    {
        $form = $this->createForm(PersonType::class, $person, [
            'action' => $this->generateUrl('app_person_create'),
            'method' => Request::METHOD_POST,
            'pathRedirect' => $pathRedirect,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Create']);

        return $form;
    }

    /**
     * @Route("/new", name="app_person_new", methods={"GET"})
     */
    public function new(string $pathRedirect = null): Response
    {
        $person = new Person();
        $form = $this->createCreateForm($person, $pathRedirect);

        return $this->render('person/new.html.twig', [
            'person' => $person,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Finds and displays a Person entity.
     *
     * @Route("/show/{id}", name="app_person_show", methods={"GET"})
     */
    public function show(Person $person, FamilyRepository $familyRepository, EntityManagerInterface $em): Response
    {
        $member = $em->getRepository(Member::class)->findOneBy(['person' => $person->getId()]);
        $teacher = $em->getRepository(Teacher::class)->findOneBy(['person' => $person->getId()]);
        $student = $em->getRepository(Student::class)->findOneBy(['person' => $person->getId()]);

        return $this->render('person/show.html.twig', [
            'person' => $person,
            'teacher' => $teacher,
            'member' => $member,
            'student' => $student,
            'families' => $familyRepository->findFamilies($person),
        ]);
    }

    /**
     * @Route("/edit/{id}", name="app_person_edit", methods={"GET"})
     */
    public function edit(Person $person): Response
    {
        $editForm = $this->createEditForm($person);

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Creates a form to edit a Person entity.
     *
     * @param Person $person The entity
     */
    private function createEditForm(Person $person): FormInterface
    {
        $form = $this->createForm(PersonType::class, $person, [
            'action' => $this->generateUrl('app_person_update', ['id' => $person->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.update']);

        return $form;
    }

    /**
     * Edits an existing Person entity.
     *
     * @Route("/update/{id}", name="app_person_update", methods={"POST", "PUT"})
     */
    public function update(Request $request, Person $person): RedirectResponse|Response
    {
        $editForm = $this->createEditForm($person);
        $editForm->handleRequest($request);

        if ($editForm->isValid()) {
            $this->getDoctrine()
                ->getManager()
                ->flush();

            $this->addFlash('success', 'The Person has been updated.');

            return $this->redirect($this->generateUrl('app_person_show', ['id' => $person->getId()]));
        }

        return $this->render('person/edit.html.twig', [
            'person' => $person,
            'edit_form' => $editForm->createView(),
        ]);
    }

    /**
     * Deletes a Person entity.
     *
     * @Route("/delete/{id}", name="app_person_delete", methods={"GET", "DELETE"})
     */
    public function delete(Request $request, Person $person): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($person->getId());
        $deleteForm->handleRequest($request);

        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->remove($person);
            $em->flush();

            $this->addFlash('success', 'The Person has been deleted.');

            return $this->redirect($this->generateUrl('app_person_index'));
        }

        return $this->render('person/delete.html.twig', [
            'person' => $person,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    /**
     * Creates a form to delete a Person entity by id.
     *
     * @param mixed $id The entity id
     */
    private function createDeleteForm(int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_person_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, ['label' => 'Delete'])
            ->getForm();
    }

    /**
     * Redirect the the list URL with the search parameter.
     *
     * @Route("/search", name="app_person_search", methods={"POST"})
     */
    public function search(Request $request): RedirectResponse
    {
        $all = $request->request->all();

        return $this->redirect($this->generateUrl('app_person_index', [
            'page' => 1,
            'search' => urlencode($all['form']['q']),
        ]));
    }

    /**
     * Finds and displays a Person entity.
     *
     * @Route("/phones/{id}", name="app_person_phones", methods={"GET"})
     */
    public function phones(Person $person): Response
    {
        $phones = PhoneManager::getAllPhones($person);

        return $this->render('person/phones.html.twig', [
            'phones' => $phones,
        ]);
    }
}
