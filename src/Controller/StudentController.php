<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Document;
use App\Entity\PackageStudentPeriod;
use App\Entity\Period;
use App\Entity\Person;
use App\Entity\Student;
use App\Entity\StudentComment;
use App\Entity\User;
use App\Exception\AppException;
use App\Exception\InvalidArgumentException;
use App\Form\PackageStudentPeriodType;
use App\Form\StudentCommentSimpleType;
use App\Form\StudentType;
use App\Manager\PeriodManager;
use App\Manager\SchoolManager;
use App\Manager\StudentManager;
use App\Repository\ClassPeriodRepository;
use App\Repository\DocumentRepository;
use App\Repository\PackageRepository;
use App\Repository\PackageStudentPeriodRepository;
use App\Repository\StudentCommentRepository;
use App\Repository\StudentRepository;
use App\Services\ResponseRequest;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Psr\Log\LoggerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

class StudentController extends AbstractController
{
    public function __construct(
        private readonly SchoolManager $schoolManager,
        private readonly PeriodManager $periodManager,
        private readonly TranslatorInterface $translator,
        private readonly LoggerInterface $logger
    ) {}

    /**
     * @throws AppException
     * @throws InvalidArgumentException
     */
    #[IsGranted('ROLE_TEACHER')]
    #[Route(path: '/student', name: 'app_student_index', methods: ['GET'])]
    public function index(StudentRepository $studentRepository, ClassPeriodRepository $classPeriodRepository): Response
    {
        $period = $this->periodManager->getPeriodsOnSession();
        $school = $this->schoolManager->getSchool();

        try {
            $students = $studentRepository->getListStudents($period, $school);
            $classPeriods = $classPeriodRepository->getClassPeriods($period, $school);
        } catch (\Throwable $throwable) {
            $this->logger->error(__METHOD__.' query failure', ['exception' => $throwable]);
            $students = [];
            $classPeriods = [];
        }

        return $this->render('student/index.html.twig', [
            'students' => $students,
            'classPeriods' => $classPeriods,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[IsGranted('ROLE_DIRECTOR')]
    #[Route(path: '/student/desactivated', name: 'app_student_desactivated', methods: ['GET'])]
    public function desactivated(StudentRepository $studentRepository, PeriodManager $periodManager, SchoolManager $schoolManager): Response
    {
        $students = $studentRepository->getListStudents($periodManager->getPeriodsOnSession(), $schoolManager->getSchool(), false);

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
    #[Route('/student/payment-list', name: 'app_student_payment_list', methods: ['GET'])]
    public function paymentList(
        StudentManager $studentManager,
        StudentRepository $studentRepository
    ): Response {
        $period = $this->periodManager->getPeriodsOnSession();
        $school = $this->schoolManager->getSchool();
        $students = $studentRepository->getPaymentList($period, $school);
        $studentsWithoutPackage = $studentRepository->getListStudentsWithoutPackagePeriod($period, $school);
        $listPayment = $studentManager->dataPaymentsStudents($students, $period);

        return $this->render('student/payment_list.html.twig', [
            'period' => $period,
            'listPayment' => $listPayment,
            'studentsWithoutPackage' => $studentsWithoutPackage,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws NonUniqueResultException
     * @throws AppException|NoResultException
     */
    #[IsGranted('ROLE_DIRECTOR')]
    #[Route(path: '/student/{id}/add-package/{period}', methods: ['GET', 'POST'])]
    public function addPackage(
        Request $request,
        StudentManager $studentManager,
        #[MapEntity(id: 'id')] Student $student,
        PackageRepository $packageRepository,
        #[MapEntity(id: 'period')] Period $period,
        SchoolManager $schoolManager
    ): Response {
        $packageStudentPeriod = (new PackageStudentPeriod())
            ->setPeriod($period)
            ->setStudent($student)
        ;
        $form = $this->createForm(PackageStudentPeriodType::class, $packageStudentPeriod)
            ->remove('student')
            ->handleRequest($request)
        ;
        $countPackage = $packageRepository->countPackages($schoolManager->getSchool());

        if (0 === $countPackage) {
            $this->addFlash('danger', $this->translator->trans('package.not_found', [
                '%url%' => $this->generateUrl('app_package_new'),
            ], 'school'));
        }

        if ($form->isSubmitted() && $form->isValid()) {
            $studentManager->addPackage($student, $packageStudentPeriod);
            $this->addFlash('success', \sprintf(
                "Le forfait %s pour l'élèves %s a bien été enregistré",
                $packageStudentPeriod->getPackage()?->getName(),
                $student->getName()
            ));

            return $this->redirectToRoute('app_student_show', [
                'id' => $student->getId(),
            ]);
        }

        if ($form->isSubmitted() && !$form->isValid()) {
            $this->addFlash('warning', \sprintf(
                "l'élève n'a pas été enregistré <br /> : %s",
                print_r($form->getErrors(), true)
            ));
        }

        return $this->render('student/add_package.html.twig', [
            'packageStudentPeriod' => $packageStudentPeriod,
            'form' => $form,
        ]);
    }

    #[Route(path: '/student/new', name: 'app_student_new', methods: ['GET'])]
    #[IsGranted('ROLE_TEACHER')]
    public function new(): Response
    {
        $student = new Student();
        $form = $this->createCreateForm($student);

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    /**
     * @throws \Exception
     */
    #[Route('/student/create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SchoolManager $schoolManager): Response
    {
        $student = new Student();

        $form = $this->createCreateForm($student)
            ->handleRequest($request)
        ;

        if ($form->isSubmitted() && $form->isValid()) {
            $student->setSchool($schoolManager->getEntitySchool());

            $entityManager->persist($student);
            $entityManager->flush();

            $this->addFlash('success', 'The Student has been created.');

            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
        }

        return $this->render('student/new.html.twig', [
            'student' => $student,
            'form' => $form,
        ]);
    }

    /**
     * @throws InvalidArgumentException
     * @throws AppException
     */
    #[Route(path: '/student/show/{id}', name: 'app_student_show', methods: ['GET'])]
    public function show(
        #[MapEntity(id: 'id')] Student $student,
        PackageStudentPeriodRepository $packageStudentPeriodRepository,
        StudentCommentRepository $studentCommentRepository,
        PeriodManager $periodManager
    ): Response {
        $formComment = $this->createCreateCommentForm(new StudentComment(), $student);
        $packagePeriods = $packageStudentPeriodRepository->getListToStudent($student);
        $comments = $studentCommentRepository->findBy(['student' => $student->getId()], ['createdAt' => 'desc']);

        return $this->render('student/show.html.twig', [
            'student' => $student,
            'comments' => $comments,
            'packagePeriods' => $packagePeriods,
            'currentPeriod' => $periodManager->getPeriodsOnSession(),
            'formComment' => $formComment->createView(),
        ]);
    }

    #[Route(path: '/student/edit/{id}', name: 'app_student_edit', methods: ['GET'])]
    public function edit(
        #[MapEntity(id: 'id')] Student $student,
    ): Response {
        $form = $this->createEditForm($student);

        return $this->render('student/edit.html.twig', [
            'form' => $form,
            'student' => $student,
        ]);
    }

    #[Route(path: '/student/update/{id}', name: 'app_student_update', methods: ['POST', 'PUT'])]
    public function update(
        Request $request,
        #[MapEntity(id: 'id')] Student $student,
        EntityManagerInterface $entityManager
    ): Response {
        $editForm = $this->createEditForm($student)
            ->handleRequest($request)
        ;

        if ($editForm->isSubmitted() && $editForm->isValid()) {
            $originalPhone = $student->getPhone();

            // S'assurer que l'entité Person est gérée par Doctrine
            if ($student->getPerson() instanceof Person) {
                $entityManager->persist($student->getPerson());
            }

            $entityManager->persist($student);
            $entityManager->flush();

            $newPhone = $student->getPhone();

            $this->addFlash('success', \sprintf(
                "Les informations de l'élève %s ont été modifiées correctement",
                (string) $student->getName()
            ));

            // Ajouter un message spécifique pour le téléphone si il a changé
            if ($originalPhone !== $newPhone && (null !== $newPhone && '' !== $newPhone && '0' !== $newPhone)) {
                $this->addFlash('info', \sprintf(
                    'Numéro de téléphone mis à jour : %s',
                    $newPhone
                ));
            }

            return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
        }

        if ($editForm->isSubmitted() && !$editForm->isValid()) {
            $this->logger->error('Form validation failed', [
                'errors' => (string) $editForm->getErrors(true),
                'student_id' => $student->getId(),
            ]);

            foreach ($editForm->getErrors(true) as $error) {
                $this->addFlash('danger', $error->getMessage());
            }
        }

        return $this->render('student/edit.html.twig', [
            'student' => $student,
            'form' => $editForm,
        ]);
    }

    #[Route(path: '/student/delete/{id}')]
    #[IsGranted('ROLE_SUPER_ADMIN')]
    public function delete(
        Request $request,
        #[MapEntity(id: 'id')] Student $student,
        EntityManagerInterface $entityManager
    ): RedirectResponse|Response {
        $deleteForm = $this->createDeleteForm($student->getId());
        $deleteForm->handleRequest($request);
        if ($deleteForm->isSubmitted() && $deleteForm->isValid()) {
            foreach ($student->getPackagePeriods() as $packagePeriod) {
                $entityManager->remove($packagePeriod);
            }

            $entityManager->remove($student);
            $entityManager->flush();

            $this->addFlash('success', 'The student has been deleted.');

            return $this->redirectToRoute('app_student_index');
        }

        return $this->render('student/delete.html.twig', [
            'student' => $student,
            'delete_form' => $deleteForm->createView(),
        ]);
    }

    #[Route(
        path: '/student/edit-status/{id}',
        name: 'app_student_edit_status',
        options: ['expose' => true],
        methods: ['POST', 'GET']
    )]
    public function editStatus(
        Request $request,
        #[MapEntity(id: 'id')] Student $student,
        EntityManagerInterface $entityManager
    ): Response {
        $student->setEnable((bool) $request->get('enable'));
        $entityManager->persist($student);
        $entityManager->flush();

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
    #[Route(path: '/student/set-image/{id}', methods: ['PUT', 'POST'])]
    public function setImage(
        Request $request,
        #[MapEntity(id: 'id')] Student $student,
        EntityManagerInterface $entityManager,
        DocumentRepository $documentRepository
    ): JsonResponse {
        $image = $documentRepository->find($request->get('document'));

        if (!$image instanceof Document) {
            throw new AppException('Not found document');
        }

        $student->setImage($image);
        $entityManager->persist($student);
        $entityManager->flush();

        return $this->json(['document' => $image->getInfos()]);
    }

    /**
     * @throws AppException
     */
    #[Route(path: '/student/set-phone/{id}', methods: ['POST', 'PUT'])]
    public function setPhone(
        #[MapEntity(id: 'id')] Student $student,
        Request $request,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $responseModel = ResponseRequest::responseDefault();
        match ($request->get('action')) {
            'delete' => $student->removePhone($request->get('key')),
            default => $student->addPhone($request->get('student_phone')),
        };

        $responseModel->data['listPhones'] = $student->getListPhones();

        $entityManager->persist($student);
        $entityManager->flush();

        return new JsonResponse($responseModel);
    }

    #[Route('/student/{id}/add-comment', methods: ['POST'])]
    public function addComment(
        Request $request,
        #[MapEntity(id: 'id')] Student $student,
        EntityManagerInterface $entityManager
    ): RedirectResponse {
        $studentComment = new StudentComment();
        $form = $this->createCreateCommentForm($studentComment, $student);
        $form->handleRequest($request);

        if ($form->isValid()) {
            if (($user = $this->getUser()) instanceof User) {
                $studentComment->setAuthor($user);
            }

            $studentComment->setStudent($student);
            $studentComment->setEnable(true);

            $entityManager->persist($studentComment);
            $entityManager->flush();

            $this->addFlash('success', 'The comment to student has been added.');
        } else {
            $this->addFlash('danger', 'The comment to student has not added beacause form is invalid.');
        }

        return $this->redirectToRoute('app_student_show', ['id' => $student->getId()]);
    }

    /**
     * @throws AppException
     */
    #[Route('/student/print/{id}/{format}/{force}', name : 'app_student_print')]
    public function print(
        #[MapEntity(id: 'id')] PackageStudentPeriod $packageStudentPeriod,
        string $format = 'html',
        bool $force = false
    ): Response {
        $pathFileTmp = implode(\DIRECTORY_SEPARATOR, [
            $this->getParameter('kernel.project_dir'),
            'public/uploads/%format%',
            str_replace('/', '_', (string) $packageStudentPeriod->getPeriod()?->getName()),
            $packageStudentPeriod->getStudent()?->getId().'.%format%',
        ]);

        $pathFileHTML = strtr($pathFileTmp, ['%format%' => 'html']);

        $this->logger->debug(__FUNCTION__, ['pathFileHTML' => $pathFileHTML, 'format' => $format, 'force' => $force]);

        if ($force || !is_file($pathFileHTML)) {
            $dir = \dirname($pathFileHTML);
            if (!file_exists($dir) && !mkdir($dir, 0o775, true) && !is_dir($dir)) {
                throw new AppException('Not create directory : '.$dir);
            }

            $html = $this->renderView('student/print.html.twig', [
                'packageStudentPeriod' => $packageStudentPeriod,
            ]);

            $put = file_put_contents($pathFileHTML, $html);
            if (0 === $put || false === $put) {
                throw new AppException('Not put the content HTML '.$pathFileHTML);
            }
        } else {
            $html = file_get_contents($pathFileHTML);
        }

        return new Response($html);
    }

    private function createCreateForm(Student $student): FormInterface
    {
        return $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_student_create'),
            'method' => Request::METHOD_POST,
        ]);
    }

    private function createCreateCommentForm(StudentComment $studentComment, Student $student): FormInterface
    {
        return $this->createForm(StudentCommentSimpleType::class, $studentComment, [
            'action' => $this->generateUrl('app_student_addcomment', ['id' => $student->getId()]),
            'method' => Request::METHOD_POST,
            'attr' => ['id' => 'student_addcomment'],
        ]);
    }

    private function createEditForm(Student $student): FormInterface
    {
        return $this->createForm(StudentType::class, $student, [
            'action' => $this->generateUrl('app_student_update', ['id' => $student->getId()]),
            'method' => Request::METHOD_PUT,
        ]);
    }

    private function createDeleteForm(?int $id): FormInterface
    {
        return $this->createFormBuilder()
            ->setAction($this->generateUrl('app_student_delete', ['id' => $id]))
            ->setMethod(Request::METHOD_DELETE)
            ->getForm()
        ;
    }
}
