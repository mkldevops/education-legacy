<?php

declare(strict_types=1);

namespace App\Controller;

use App\Controller\Api\FamilyApiController;
use App\Controller\Base\AbstractBaseController;
use App\Entity\ClassPeriod;
use App\Entity\Document;
use App\Entity\Family;
use App\Entity\Package;
use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Student;
use App\Entity\StudentComment;
use App\Entity\User;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Form\FamilyType;
use App\Form\PackageStudentPeriodType;
use App\Form\StudentCommentSimpleType;
use App\Form\StudentType;
use App\Manager\DocumentManager;
use App\Manager\StudentManager;
use App\Repository\PackageRepository;
use App\Repository\PackageStudentPeriodRepository;
use App\Repository\StudentCommentRepository;
use App\Services\ResponseRequest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\ORMException;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @since  0.2
 *
 * @author Hamada Sidi Fahari <h.fahari@gmail.com>
 */
#[Route(path: '/student')]
class StudentController extends AbstractBaseController
{
    /**
     * @IsGranted("ROLE_TEACHER")
     *
     * @throws InvalidArgumentException
     */
    #[Route(path: '', name: 'app_student_index', methods: ['GET'])]
    public function index(): Response
    {
        $period = $this->getPeriod();
        $school = $this->getSchool();
        $manager = $this->getDoctrine()->getManager();
        $students = $manager->getRepository(Student::class)
            ->getListStudents($period, $school);
        $classPeriods = $manager->getRepository(ClassPeriod::class)
            ->getClassPeriods($period, $school);
        return $this->render('student/index.html.twig', [
            'students' => $students,
            'classPeriods' => $classPeriods,
        ]);
    }
    /**
     * @IsGranted("ROLE_DIRECTOR")
     *
     * @throws InvalidArgumentException
     */
    #[Route(path: '/desactivated', methods: ['GET'], name: 'app_student_desactivated')]
    public function desactivated(): Response
    {
        $students = $this->getManager()
            ->getRepository(Student::class)
            ->getListStudents($this->getPeriod(), $this->getSchool(), false);
        return $this->render('student/index.html.twig', [
            'students' => $students,
        ]);
    }
    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[IsGranted('ROLE_ACCOUNTANT')]
    #[IsGranted('ROLE_DIRECTOR')]
    #[Route("/payment-list", name: "app_student_payment_list", methods: ["GET"])]
    public function paymentList(EntityManagerInterface $manager, StudentManager $studentManager): Response
    {
        ini_set('memory_limit', '-1');

        $period = $this->getPeriod();
        $school = $this->getSchool();

        $students = $manager->getRepository(Student::class)
            ->getPaymentList($period, $school);

        $studentsWithoutPackage = $manager->getRepository(Student::class)
            ->getListStudentsWithoutPackagePeriod($period, $school);

        $listPayment = $studentManager->dataPayementsStudents($students, $period);

        return $this->render('student/payment_list.html.twig', [
            'period' => $this->getPeriod(),
            'listPayment' => $listPayment,
            'studentsWithoutPackage' => $studentsWithoutPackage,
        ]);
    }
    /**
     * @IsGranted("ROLE_DIRECTOR")
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     * @throws AppException|NoResultException
     */
    #[Route(path: '/{id}/add-package/{period}', methods: ['GET', 'POST'])]
    public function addPackage(
        Request $request,
        StudentManager $studentManager,
        Student $student,
        PackageRepository $packageRepository,
        Period $period = null
    ): Response {
        $packageStudentPeriod = (new PackageStudentPeriod())
            ->setPeriod($period ?? $this->getEntityPeriod())
            ->setStudent($student);
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
            ->add('submit', SubmitType::class, ['label' => 'Create'])
            ->remove('student')
            ->handleRequest($request);
        $countPackage = $packageRepository->countPackages($this->getSchool());

        if (empty($countPackage)) {
            $this->addFlash('danger', $this->trans('package.not_found', [
                '%url%' => $this->generateUrl('app_package_new'),
            ], 'school'));
        }
        if ($form->isSubmitted() && $form->isValid()) {
            $studentManager->addPackage($student, $packageStudentPeriod);
            $this->addFlash('success', sprintf(
                "Le forfait %s pour l'élèves %s a bien été enregistré",
                (string) $packageStudentPeriod->getPackage()?->getName(),
                (string) $student->getName()
            ));

            return $this->redirect($this->generateUrl('app_student_show', [
                'id' => $student->getId(),
            ]));
        }
        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', sprintf(
                'l\'élève n\'a pas été enregistré <br /> : %s',
                print_r($form->getErrors(), true)
            ));
        }
        return $this->render('student/add_package.html.twig', [
            'packageStudentPeriod' => $packageStudentPeriod,
            'form' => $form->createView(),
        ]);
    }
    /**
     * @IsGranted("ROLE_TEACHER")
     */
    #[Route(path: '/new', name: 'app_student_new', methods: ['GET'])]
    public function new(): Response
    {
        $student = new Student();
        $form = $this->createCreateForm($student);
        $formFamily = $this->createCreateFormFamily();
        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form->createView(),
            'formFamily' => $formFamily->createView(),
        ]);
    }
    private function createCreateForm(Student $student): FormInterface
    {
        $this->logger->info(__METHOD__);

        $form = $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_student_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.create']);

        $this->logger->debug(__FUNCTION__, ['form' => $form]);

        return $form;
    }
    private function createCreateFormFamily(): FormInterface
    {
        $family = new Family();
        $form = $this->createForm(FamilyType::class, $family, [
            'action' => $this->generateUrl('app_api_family_create'),
            'method' => Request::METHOD_POST,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'form.button.create']);

        return $form;
    }
    /**
     * @throws Exception
     */
    #[Route("/create", methods: ["POST"])]
    public function create(Request $request, FamilyApiController $apiController, EntityManagerInterface $em): Response
    {
        $this->logger->info(__METHOD__);
        $student = new Student();

        $formFamily = $apiController->createCreateForm(new Family());
        $form = $this->createCreateForm($student)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setAuthor($this->getUser())
                ->setSchool($this->getEntitySchool());

            $em->persist($student);
            $em->flush();

            $this->addFlash('success', 'The Student has been created.');

            return $this->redirect($this->generateUrl('app_student_show', ['id' => $student->getId()]));
        }

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form->createView(),
            'formFamily' => $formFamily->createView(),
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[Route(path: '/show/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(
        Student $student,
        PackageStudentPeriodRepository $packageStudentPeriodRepository,
        StudentCommentRepository $studentCommentRepository,
    ): Response {
        $formComment = $this->createCreateCommentForm(new studentComment(), $student);
        $packagePeriods = $packageStudentPeriodRepository->getListToStudent($student);
        $comments = $studentCommentRepository->findBy(['student' => $student->getId()], ['createdAt' => 'desc']);

        return $this->render('student/show.html.twig', [
            'student' => $student,
            'comments' => $comments,
            'packagePeriods' => $packagePeriods,
            'currentPeriod' => $this->getPeriod(),
            'formComment' => $formComment->createView(),
        ]);
    }

    private function createCreateCommentForm(StudentComment $comment, Student $student): FormInterface
    {
        return $this->createForm(StudentCommentSimpleType::class, $comment, [
            'action' => $this->generateUrl('app_student_addcomment', ['id' => $student->getId()]),
            'method' => Request::METHOD_POST,
            'attr' => ['id' => 'student_addcomment'],
        ]);
    }

    #[Route(path: '/edit/{id}', name: 'app_student_edit', methods: ['GET'])]
    public function edit(Student $student, FamilyApiController $apiController): Response
    {
        $form = $this->createEditForm($student);
        $formFamily = $apiController->createEditForm($student->getFamily());
        return $this->render('student/edit.html.twig', [
            'formFamily' => $formFamily->createView(),
            'form' => $form->createView(),
            'student' => $student,
        ]);
    }

    private function createEditForm(Student $student): FormInterface
    {
        $form = $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_student_update', ['id' => $student->getId()]),
            'method' => Request::METHOD_PUT,
        ]);

        $form->add('submit', SubmitType::class, ['label' => 'Update']);

        return $form;
    }

    #[Route(path: '/update/{id}', name: 'app_student_update', methods: ['POST', 'PUT'])]
    public function update(Request $request, Student $student): Response
    {
        $editForm = $this->createEditForm($student)
            ->handleRequest($request);
        $formFamily = $this->createCreateFormFamily()
            ->handleRequest($request);
        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $manager = $this->getDoctrine()->getManager();

            $manager->flush();

            // Reste de la méthode qu'on avait déjà écrit
            $this->addFlash('info', sprintf(
                "les information de l'élève %s  ont été modifié correctement",
                (string) $student->getName()
            ));

            return $this->redirect($this->generateUrl('app_student_show', ['id' => $student->getId()]));
        }
        return $this->render('student/edit.html.twig', [
            'formFamily' => $formFamily->createView(),
            'student' => $student,
            'form' => $editForm->createView(),
        ]);
    }

    #[Route(path: '/delete/{id}')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(Request $request, Student $student, EntityManagerInterface $em): RedirectResponse|Response
    {
        $deleteForm = $this->createDeleteForm($student->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            foreach ($student->getPackagePeriods() as $packagePeriod) {
                $em->remove($packagePeriod);
            }

            $em->remove($student);
            $em->flush();

            $this->addFlash('success', 'The student has been deleted.');

            return $this->redirect($this->generateUrl('app_student_index'));
        }
        return $this->render('student/delete.html.twig', [
            'student' => $student,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    private function createDeleteForm(?int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_student_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->add('submit', SubmitType::class, [
                'label' => 'Delete',
                'attr' => ['class' => 'btn btn-danger'],
            ])
            ->getForm();
    }


    #[Route(
        path: '/edit-status/{id}',
        name: 'app_student_edit_status',
        options: ['expose' => true],
        methods: ['POST', 'GET']
    )]
    public function editStatus(Request $request, Student $student, EntityManagerInterface $em): Response
    {
        $student->setEnable((bool) $request->get('enable'));
        $em->persist($student);
        $em->flush();

        $redirectRequest = (bool) $request->get('redirect', false);
        if ($redirectRequest) {
            $this->addFlash('success', 'The status student is updated');
            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
        }

        return $this->json([
            'success' => true,
            'enable' => $student->getEnable(),
            'form' => $request->get('enable'),
        ]);
    }

    /**
     * @throws AppException
     * @throws \ImagickException
     */
    #[Route(path: '/set-image/{id}', methods: ['PUT', 'POST'])]
    public function setImage(Request $request, Student $student, EntityManagerInterface $em): JsonResponse
    {
        $response = ResponseRequest::responseDefault(['document' => null]);
        /* @var $image Document */
        $image = $em->getRepository(Document::class)->find($request->get('document'));

        if ($image === null) {
            throw new AppException('Not found document');
        }

        $student->setImage($image);
        $em->persist($student);
        $em->flush();

        $response->document = $image->getInfos();
        return $this->json($response);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/set-phone/{id}', methods: ['POST', 'PUT'])]
    public function setPhone(Student $student, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $response = ResponseRequest::responseDefault();
        switch ($request->get('action')) {
            case 'delete':
                $student->removePhone($request->get('key'));
                break;

            case 'add':
            default:
                $student->addPhone($request->get('student_phone'));
                break;
        }

        $response->data['listPhones'] = $student->getListPhones();

        $em->persist($student);
        $em->flush();
        return new JsonResponse($response);
    }

    #[Route("/{id}/add-comment", methods:["POST"])]
    public function addComment(Request $request, Student $student, EntityManagerInterface $manager): RedirectResponse
    {
        $studentComment = new StudentComment();
        $form = $this->createCreateCommentForm($studentComment, $student);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (($user = $this->getUser()) instanceof User) {
                $studentComment->setAuthor($user);
            }
            $studentComment->setStudent($student);
            $studentComment->setEnable(true);

            $manager->persist($studentComment);
            $manager->flush();

            $this->addFlash('success', 'The comment to student has been added.');
        } else {
            $this->addFlash('danger', 'The comment to student has not added beacause form is invalid.');
        }

        return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
    }

    /**
     * @throws AppException
     */
    #[Route("/print/{id}/{format}/{force}", name : "app_student_print")]
    public function print(PackageStudentPeriod $pkgStudent, string $format = 'html', bool $force = false): Response
    {
        $pathFileTmp = implode(DIRECTORY_SEPARATOR, [
            $this->getParameter('kernel.project_dir'),
            'public/uploads/%format%',
            str_replace('/', '_', (string) $pkgStudent->getPeriod()?->getName()),
            $pkgStudent->getStudent()?->getId() . '.%format%',
        ]);

        $pathFileHTML = strtr($pathFileTmp, ['%format%' => 'html']);

        $this->logger->debug(__FUNCTION__, ['pathFileHTML' => $pathFileHTML, 'format' => $format, 'force' => $force]);

        if ($force || !is_file($pathFileHTML)) {
            $dir = dirname($pathFileHTML);
            if (!file_exists($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
                throw new AppException('Not create directory : ' . $dir);
            }

            $html = $this->renderView('student/print.html.twig', [
                'packageStudentPeriod' => $pkgStudent,
            ]);

            $put = file_put_contents($pathFileHTML, $html);
            if (empty($put)) {
                throw new AppException('Not put the content HTML ' . $pathFileHTML);
            }
        } else {
            $html = file_get_contents($pathFileHTML);
        }

        return new Response($html);
    }
}
