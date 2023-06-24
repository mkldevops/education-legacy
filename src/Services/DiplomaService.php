<?php

declare(strict_types=1);

namespace App\Services;

use App\Entity\Period;
use App\Entity\School;
use App\Repository\PeriodRepository;
use App\Repository\StudentRepository;
use Imagick;
use ImagickDraw;
use ImagickDrawException;
use ImagickException;
use ImagickPixel;
use Symfony\Component\HttpFoundation\File\File;

class DiplomaService
{
    /**
     * @var string
     */
    private const FONT_MILLENIA = 'millenia.ttf';

    /**
     * @var string
     */
    private const FONT_SIGNATARA = 'signatra.otf';

    private File $file;

    private string $pathFont;

    private string $pathUploads;

    public function __construct(
        private PeriodRepository $periodRepository,
        private StudentRepository $studentRepository
    ) {
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     */
    public function generate(School $school, Period $period = null, int $limit = 1): bool
    {
        if (null === $period) {
            $period = $this->periodRepository->getCurrentPeriod();
        }

        $list = $this->studentRepository->getListStudents($period, $school, true, $limit);

        foreach ($list as $item) {
            $this->getDiplomaStudent($item->__toString(), $item->getId());
        }

        return true;
    }

    /**
     * @throws ImagickException
     * @throws ImagickDrawException
     */
    public function getDiplomaStudent(string $student, string|int $id = null): Imagick
    {
        $id = $id ?: substr(md5($student), 0, 4);

        $image = new Imagick($this->file->getRealPath());

        $draw = new ImagickDraw();
        $draw->setFont($this->pathFont.\DIRECTORY_SEPARATOR.self::FONT_MILLENIA);
        $draw->setFontSize(200);
        $draw->setFontWeight(900);
        $draw->setFillColor(new ImagickPixel('#444'));
        $draw->setStrokeColor(new ImagickPixel('#f95000'));
        $draw->setStrokeWidth(1);
        $draw->setTextAlignment(Imagick::ALIGN_CENTER);
        $draw->setGravity(Imagick::GRAVITY_NORTHEAST);

        $x = $image->getImageWidth() / 2;
        $y = $image->getImageHeight() / 1.50;

        $draw->annotation($x, $y, wordwrap(ucwords(strtolower($student)), 20, "\n"));

        $draw->setFontSize(250);
        $draw->setFont($this->pathFont.\DIRECTORY_SEPARATOR.self::FONT_SIGNATARA);

        $x = $image->getImageWidth() / 5.5;
        $y = $image->getImageHeight() / 1.11;

        $draw->annotation($x, $y, 'le 7 Juillet 2019');

        $image->drawImage($draw);
        $image->setImageFormat('jpeg');

        $image->writeImage($this->pathUploads.\DIRECTORY_SEPARATOR.$id.'.jpeg');

        return $image;
    }

    public function setPathFont(string $pathFont): self
    {
        $this->pathFont = $pathFont;

        return $this;
    }

    public function setFileFromPath(string $path): self
    {
        return $this->setFile(new File($path));
    }

    public function setFile(File $file): self
    {
        $this->file = $file;

        return $this;
    }

    public function setPathUploads(string $pathUploads): static
    {
        $this->pathUploads = $pathUploads;

        return $this;
    }
}
