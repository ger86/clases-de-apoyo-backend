<?php

namespace App\Service\Exam;

use App\Entity\Exam;

final class EnhancedExamPageCatalog
{
    /**
     * @return array<string, mixed>|null
     */
    public function findForExam(Exam $exam): ?array
    {
        $key = $this->buildExamKey($exam);
        if ($key === null) {
            return null;
        }

        $page = $this->pages()[$key] ?? null;
        if (!$this->isComplete($page)) {
            return null;
        }

        return $page;
    }

    private function buildExamKey(Exam $exam): ?string
    {
        $testYear = $exam->getTestYear();
        $communityTestCourseSubject = $testYear->getCommunityTestCourseSubject();
        $communityTest = $communityTestCourseSubject?->getCommunityTest();
        $courseSubject = $communityTestCourseSubject?->getCourseSubject();
        $knowledgeTest = $communityTest?->getKnowledgeTest();
        $community = $communityTest?->getCommunity();

        if ($communityTestCourseSubject === null || $communityTest === null || $courseSubject === null || $knowledgeTest === null || $community === null) {
            return null;
        }

        return implode('/', [
            $knowledgeTest->getSlug(),
            $community->getSlug(),
            $courseSubject->getSubjectSlug(),
            $exam->getSlug(),
        ]);
    }

    /**
     * @param array<string, mixed>|null $page
     */
    private function isComplete(?array $page): bool
    {
        if ($page === null) {
            return false;
        }

        foreach ($this->requiredFields() as $field) {
            if (!$this->hasContent($page, $field)) {
                return false;
            }
        }

        if (!$this->hasRows($page, 'examData', ['label', 'value'])) {
            return false;
        }

        if (!$this->hasRows($page, 'questions', ['block', 'question', 'task', 'topic'])) {
            return false;
        }

        if (!$this->hasRows($page, 'relatedExams', ['label', 'type'])) {
            return false;
        }

        foreach ($page['relatedExams'] as $relatedExam) {
            if (is_array($relatedExam) && ($relatedExam['type'] ?? null) === 'exam' && !$this->hasContent($relatedExam, 'examSlug')) {
                return false;
            }
        }

        if (!$this->hasRows($page, 'solutionCta', ['eyebrow', 'title', 'text', 'priceText', 'buttonLabel', 'eventLabel'])) {
            return false;
        }

        return true;
    }

    /**
     * @param array<string, mixed> $page
     */
    private function hasContent(array $page, string $field): bool
    {
        if (!array_key_exists($field, $page) || $page[$field] === null || $page[$field] === '') {
            return false;
        }

        return !is_array($page[$field]) || $page[$field] !== [];
    }

    /**
     * @param array<string, mixed> $page
     * @param list<string> $fields
     */
    private function hasRows(array $page, string $field, array $fields): bool
    {
        if (!$this->hasContent($page, $field) || !is_array($page[$field])) {
            return false;
        }

        $rows = $field === 'solutionCta' ? [$page[$field]] : $page[$field];
        foreach ($rows as $row) {
            if (!is_array($row)) {
                return false;
            }

            foreach ($fields as $rowField) {
                if (!$this->hasContent($row, $rowField)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * @return list<string>
     */
    private function requiredFields(): array
    {
        return [
            'metaTitle',
            'title',
            'metaDescription',
            'summaryParagraphs',
            'solutionCta',
            'statementTitle',
            'visibleFileLabel',
            'lockedFileLabel',
            'premiumFileLabel',
            'examData',
            'questionsTitle',
            'questions',
            'topics',
            'practiceSteps',
            'relatedExams',
            'quickFacts',
            'educationalLevel',
            'schemaAbout',
            'learningResourceTypes',
            'analyticsLabel',
            'authorName',
            'authorJobTitle',
        ];
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function pages(): array
    {
        return [
            'selectividad/madrid/matematicas/2025-modelo' => [
                'metaTitle' => 'PAU Madrid 2025 Modelo Matemáticas II | Enunciado y solución',
                'title' => 'PAU Madrid 2025 Modelo Matemáticas II: enunciado, temas y solución',
                'metaDescription' => 'Consulta el modelo PAU/EvAU Madrid 2025 de Matemáticas II: enunciado, datos del examen, bloques, preguntas, temas, dificultad y acceso a la solución.',
                'summaryTitle' => 'Resumen del examen',
                'summaryParagraphs' => [
                    'Este modelo PAU/EvAU de Matemáticas II de Madrid para el curso 2024/2025 reúne cuatro bloques: matrices y sistemas de ecuaciones, análisis de funciones e integrales, geometría analítica en el espacio y probabilidad. El examen dura 90 minutos y cada bloque cuenta 2,5 puntos.',
                    'Los tres primeros bloques ofrecen dos preguntas alternativas y el cuarto bloque incluye una pregunta obligatoria de probabilidad binomial con aproximación normal. En esta página puedes abrir el enunciado, revisar los temas de cada apartado y acceder a la solución dentro del pack PAU de Matemáticas II Madrid.',
                ],
                'solutionCta' => [
                    'eyebrow' => 'Solución del modelo 2025',
                    'title' => 'Corrige este examen con la solución completa',
                    'text' => 'El pack PAU {subjectName} {communityName} incluye la solución de este modelo y el histórico {yearRange} de enunciados y soluciones para practicar sin buscar año por año.',
                    'priceText' => 'Pago único de {formattedPrice}, sin suscripción.',
                    'buttonLabel' => 'Ver solución y pack completo',
                    'eventLabel' => 'exam-main-solution-cta-seo-madrid-math-2025-modelo',
                ],
                'statementTitle' => 'Enunciado y solución',
                'visibleFileLabel' => 'Ver enunciado oficial del modelo 2025',
                'visibleFileTitle' => 'Ver el enunciado del modelo PAU Madrid 2025 de Matemáticas II',
                'lockedFileLabel' => 'Ver solución completa en el pack PAU {subjectName} {communityName}',
                'premiumFileLabel' => 'Ver {fileName} con Premium',
                'examDataTitle' => 'Datos del examen',
                'examData' => [
                    ['label' => 'Prueba', 'value' => 'PAU/EvAU Madrid'],
                    ['label' => 'Asignatura', 'value' => 'Matemáticas II'],
                    ['label' => 'Curso', 'value' => '2024/2025'],
                    ['label' => 'Convocatoria', 'value' => 'Modelo'],
                    ['label' => 'Duración', 'value' => '90 minutos'],
                    ['label' => 'Calificación', 'value' => '4 bloques de 2,5 puntos cada uno'],
                    ['label' => 'Dificultad estimada', 'value' => '{difficulty}/10'],
                ],
                'questionsTitle' => 'Preguntas del modelo PAU Madrid 2025 de Matemáticas II',
                'questions' => [
                    ['block' => '1', 'question' => '1.1', 'task' => 'Estudiar la inversa de AB, el rango de BA y discutir un sistema con parámetros.', 'topic' => 'Matrices, rango y sistemas'],
                    ['block' => '1', 'question' => '1.2', 'task' => 'Plantear y resolver un sistema de ecuaciones sobre capacidades de garrafas y un aljibe.', 'topic' => 'Sistemas lineales aplicados'],
                    ['block' => '2', 'question' => '2.1', 'task' => 'Estudiar continuidad, extremos relativos y área bajo una función definida a trozos.', 'topic' => 'Funciones, derivadas e integrales'],
                    ['block' => '2', 'question' => '2.2', 'task' => 'Analizar una función trigonométrica, calcular un límite y resolver una integral por partes.', 'topic' => 'Trigonometría, límites e integración'],
                    ['block' => '3', 'question' => '3.1', 'task' => 'Hallar un plano de simetría, un plano que contiene una recta y una recta paralela dada.', 'topic' => 'Geometría analítica en el espacio'],
                    ['block' => '3', 'question' => '3.2', 'task' => 'Calcular ángulos entre planos, intersección de tres planos y proyecciones ortogonales.', 'topic' => 'Planos, ángulos y proyecciones'],
                    ['block' => '4', 'question' => '4', 'task' => 'Resolver probabilidades con vacunación de gripe, reuniones de 5 y 7 personas, y aproximación normal para una muestra de 500.', 'topic' => 'Distribución binomial y normal'],
                ],
                'topicsTitle' => 'Temas que aparecen',
                'topics' => [
                    'Matrices con parámetro, existencia de inversa, rango y discusión de sistemas.',
                    'Modelización mediante sistemas de ecuaciones lineales.',
                    'Continuidad, extremos relativos y cálculo de áreas con integrales definidas.',
                    'Funciones trigonométricas, paridad, límites e integración por partes.',
                    'Planos, rectas, simetrías, ángulos e intersecciones en el espacio.',
                    'Probabilidad binomial, restricciones por probabilidad y aproximación normal.',
                ],
                'practiceTitle' => 'Cómo practicar este examen',
                'practiceSteps' => [
                    'Haz primero una pregunta de cada bloque respetando el límite de 90 minutos.',
                    'Marca los apartados en los que dependes de una fórmula o de un procedimiento que no recuerdas.',
                    'Corrige el examen con la solución y repite solo los bloques con errores de planteamiento.',
                    'Refuerza matrices, análisis, geometría y probabilidad con otros modelos de Madrid.',
                ],
                'relatedTitle' => 'Exámenes relacionados',
                'relatedExams' => [
                    ['label' => 'Todos los exámenes de PAU Matemáticas II Madrid', 'type' => 'subject'],
                    ['label' => 'PAU Madrid 2025 Matemáticas II junio', 'type' => 'exam', 'examSlug' => '2025-junio-1'],
                    ['label' => 'Modelo PAU Madrid 2024 Matemáticas II', 'type' => 'exam', 'examSlug' => '2024-modelo-2'],
                ],
                'quickFactsTitle' => 'Ficha rápida',
                'quickFacts' => [
                    'Comunidad: Madrid',
                    'Asignatura: Matemáticas II',
                    'Convocatoria: Modelo 2025',
                    'Nivel: 2º Bachillerato',
                    'Tiempo: 90 minutos',
                ],
                'educationalLevel' => '2º Bachillerato',
                'learningResourceTypes' => ['Examen PAU', 'Modelo de examen', 'Solución de examen'],
                'schemaAbout' => [
                    'PAU Madrid',
                    'EvAU Madrid',
                    'Matemáticas II',
                    'Matrices y sistemas de ecuaciones',
                    'Continuidad e integrales',
                    'Geometría analítica en el espacio',
                    'Probabilidad binomial y aproximación normal',
                ],
                'analyticsLabel' => 'exam-seo-madrid-math-2025-modelo',
                'sidebarPackEventLabel' => 'exam-sidebar-seo-madrid-math-2025-modelo',
                'authorName' => 'Juan Carlos Rojo',
                'authorJobTitle' => 'Profesor de apoyo especializado en PAU, Bachillerato y ESO',
            ],
        ];
    }
}
