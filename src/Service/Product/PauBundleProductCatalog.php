<?php

namespace App\Service\Product;

use App\Entity\CommunityTestCourseSubject;
use App\Entity\CourseSubject;
use App\Entity\Exam;
use App\Entity\File;
use App\Entity\Product;

final class PauBundleProductCatalog
{
    /** @var array<string, PauBundleProductDefinition>|null */
    private ?array $definitions = null;

    public function findByCode(string $code): ?PauBundleProductDefinition
    {
        return $this->getDefinitions()[$code] ?? null;
    }

    public function findBySlug(string $slug): ?PauBundleProductDefinition
    {
        foreach ($this->getDefinitions() as $definition) {
            if ($definition->getSlug() === $slug) {
                return $definition;
            }
        }

        return null;
    }

    public function findByProduct(Product $product): ?PauBundleProductDefinition
    {
        return $this->findByCode($product->getCode());
    }

    public function findByExam(Exam $exam): ?PauBundleProductDefinition
    {
        return $this->findByCommunityTestCourseSubject($exam->getTestYear()->getCommunityTestCourseSubject());
    }

    public function findByFile(File $file): ?PauBundleProductDefinition
    {
        $exam = $file->getExam();
        if ($exam !== null) {
            return $this->findByExam($exam);
        }

        $chapter = $file->getChapter();
        if ($chapter === null || $chapter->getChapterBlock() === null) {
            return null;
        }

        return $this->findByCourseSubject($chapter->getChapterBlock()->getCourseSubject());
    }

    public function findByCommunityTestCourseSubject(CommunityTestCourseSubject $communityTestCourseSubject): ?PauBundleProductDefinition
    {
        $communityTest = $communityTestCourseSubject->getCommunityTest();
        $courseSubject = $communityTestCourseSubject->getCourseSubject();

        foreach ($this->getDefinitions() as $definition) {
            if ($communityTest->getCommunity()->getSlug() === $definition->getCommunitySlug()
                && $communityTest->getKnowledgeTest()->getSlug() === $definition->getKnowledgeTestSlug()
                && $this->courseSubjectMatchesDefinition($courseSubject, $definition)
            ) {
                return $definition;
            }
        }

        return null;
    }

    public function findByCourseSubject(CourseSubject $courseSubject): ?PauBundleProductDefinition
    {
        foreach ($this->getDefinitions() as $definition) {
            if ($this->courseSubjectMatchesDefinition($courseSubject, $definition)) {
                return $definition;
            }
        }

        return null;
    }

    /**
     * @return array<string, PauBundleProductDefinition>
     */
    public function getDefinitions(): array
    {
        if ($this->definitions === null) {
            $this->definitions = $this->createDefinitions();
        }

        return $this->definitions;
    }

    private function courseSubjectMatchesDefinition(CourseSubject $courseSubject, PauBundleProductDefinition $definition): bool
    {
        return $courseSubject->getCourse()?->getSlug() === $definition->getCourseSlug()
            && $courseSubject->getSubjectSlug() === $definition->getSubjectSlug();
    }

    /**
     * @return array<string, PauBundleProductDefinition>
     */
    private function createDefinitions(): array
    {
        $definitions = [
            new PauBundleProductDefinition(
                code: 'pau_matematicas_ii_madrid_1994_2025',
                slug: 'pau-matematicas-ii-madrid-1994-2025',
                title: 'Pack PAU Matemáticas II Madrid 1994-2025',
                subjectSlug: 'matematicas',
                subjectName: 'Matemáticas II',
                communitySlug: 'madrid',
                communityName: 'Madrid',
                knowledgeTestSlug: 'selectividad',
                courseSlug: '2o-bachillerato',
                yearRange: '1994-2025',
                priceCents: 999,
                currency: 'eur',
                description: <<<HTML
<p>Prepara la PAU/EvAU de Matemáticas II de Madrid con un único pack descargable que reúne exámenes reales desde 1994 hasta 2025.</p>
<p>Incluye enunciados, soluciones y un PDF completo para estudiar de forma intensiva sin tener que navegar año por año.</p>
HTML,
                statementCount: 88,
                solutionCount: 81,
                completePages: 943,
                statementPages: 194,
                solutionPages: 749,
                files: [
                    [
                        'key' => 'complete',
                        'label' => 'Pack completo: exámenes y soluciones',
                        'description' => '943 páginas.',
                        'path' => 'product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-examenes-y-soluciones.pdf',
                        'filename' => 'PAU-Matematicas-II-Madrid-1994-2025-examenes-y-soluciones.pdf',
                    ],
                    [
                        'key' => 'enunciados',
                        'label' => 'Solo enunciados',
                        'description' => '194 páginas.',
                        'path' => 'product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-enunciados.pdf',
                        'filename' => 'PAU-Matematicas-II-Madrid-1994-2025-enunciados.pdf',
                    ],
                    [
                        'key' => 'soluciones',
                        'label' => 'Solo soluciones',
                        'description' => '749 páginas.',
                        'path' => 'product-downloads/pau-matematicas-ii-madrid-1994-2025/PAU-Matematicas-II-Madrid-1994-2025-soluciones.pdf',
                        'filename' => 'PAU-Matematicas-II-Madrid-1994-2025-soluciones.pdf',
                    ],
                ]
            ),
            new PauBundleProductDefinition(
                code: 'pau_fisica_madrid_1996_2025',
                slug: 'pau-fisica-madrid-1996-2025',
                title: 'Pack PAU Física Madrid 1996-2025',
                subjectSlug: 'fisica',
                subjectName: 'Física',
                communitySlug: 'madrid',
                communityName: 'Madrid',
                knowledgeTestSlug: 'selectividad',
                courseSlug: '2o-bachillerato',
                yearRange: '1996-2025',
                priceCents: 999,
                currency: 'eur',
                description: <<<HTML
<p>Prepara la PAU/EvAU de Física de Madrid con un único pack descargable que reúne exámenes reales desde 1996 hasta 2025.</p>
<p>Incluye enunciados, soluciones y un PDF completo para estudiar de forma intensiva sin tener que navegar año por año.</p>
HTML,
                statementCount: 78,
                solutionCount: 70,
                completePages: 786,
                statementPages: 170,
                solutionPages: 616,
                files: [
                    [
                        'key' => 'complete',
                        'label' => 'Pack completo: exámenes y soluciones',
                        'description' => '786 páginas.',
                        'path' => 'product-downloads/pau-fisica-madrid-1996-2025/PAU-Fisica-Madrid-1996-2025-examenes-y-soluciones.pdf',
                        'filename' => 'PAU-Fisica-Madrid-1996-2025-examenes-y-soluciones.pdf',
                    ],
                    [
                        'key' => 'enunciados',
                        'label' => 'Solo enunciados',
                        'description' => '170 páginas.',
                        'path' => 'product-downloads/pau-fisica-madrid-1996-2025/PAU-Fisica-Madrid-1996-2025-enunciados.pdf',
                        'filename' => 'PAU-Fisica-Madrid-1996-2025-enunciados.pdf',
                    ],
                    [
                        'key' => 'soluciones',
                        'label' => 'Solo soluciones',
                        'description' => '616 páginas.',
                        'path' => 'product-downloads/pau-fisica-madrid-1996-2025/PAU-Fisica-Madrid-1996-2025-soluciones.pdf',
                        'filename' => 'PAU-Fisica-Madrid-1996-2025-soluciones.pdf',
                    ],
                ]
            ),
            new PauBundleProductDefinition(
                code: 'pau_quimica_madrid_1996_2025',
                slug: 'pau-quimica-madrid-1996-2025',
                title: 'Pack PAU Química Madrid 1996-2025',
                subjectSlug: 'quimica',
                subjectName: 'Química',
                communitySlug: 'madrid',
                communityName: 'Madrid',
                knowledgeTestSlug: 'selectividad',
                courseSlug: '2o-bachillerato',
                yearRange: '1996-2025',
                priceCents: 999,
                currency: 'eur',
                description: <<<HTML
<p>Prepara la PAU/EvAU de Química de Madrid con un único pack descargable que reúne exámenes reales desde 1996 hasta 2025.</p>
<p>Incluye enunciados, soluciones y un PDF completo para estudiar de forma intensiva sin tener que navegar año por año.</p>
HTML,
                statementCount: 74,
                solutionCount: 65,
                completePages: 708,
                statementPages: 168,
                solutionPages: 540,
                files: [
                    [
                        'key' => 'complete',
                        'label' => 'Pack completo: exámenes y soluciones',
                        'description' => '708 páginas.',
                        'path' => 'product-downloads/pau-quimica-madrid-1996-2025/PAU-Quimica-Madrid-1996-2025-examenes-y-soluciones.pdf',
                        'filename' => 'PAU-Quimica-Madrid-1996-2025-examenes-y-soluciones.pdf',
                    ],
                    [
                        'key' => 'enunciados',
                        'label' => 'Solo enunciados',
                        'description' => '168 páginas.',
                        'path' => 'product-downloads/pau-quimica-madrid-1996-2025/PAU-Quimica-Madrid-1996-2025-enunciados.pdf',
                        'filename' => 'PAU-Quimica-Madrid-1996-2025-enunciados.pdf',
                    ],
                    [
                        'key' => 'soluciones',
                        'label' => 'Solo soluciones',
                        'description' => '540 páginas.',
                        'path' => 'product-downloads/pau-quimica-madrid-1996-2025/PAU-Quimica-Madrid-1996-2025-soluciones.pdf',
                        'filename' => 'PAU-Quimica-Madrid-1996-2025-soluciones.pdf',
                    ],
                ]
            ),
        ];

        $indexedDefinitions = [];
        foreach ($definitions as $definition) {
            $indexedDefinitions[$definition->getCode()] = $definition;
        }

        return $indexedDefinitions;
    }
}
