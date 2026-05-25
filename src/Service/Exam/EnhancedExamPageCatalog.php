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
        ] + $this->madridMatematicasPages() + $this->madridFisicaPages() + $this->madridQuimicaPages();
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function madridMatematicasPages(): array
    {
        $entries = [
            ['2025-junio-1', '2025 Junio', '2024/2025', 'Junio 2025', 'ordinaria', [
                ['1', 'Álgebra', 'Problemas de sistemas lineales aplicados a tiros de baloncesto y matrices con polinomio característico.', 'Sistemas lineales, matrices y autovalores'],
                ['2', 'Análisis', 'Analizar una función trigonométrica en un muro, estudiar extremos y calcular áreas mediante integrales.', 'Funciones trigonométricas, extremos e integrales'],
                ['3', 'Geometría', 'Resolver cuestiones de rectas, planos, distancias, perpendicularidad y áreas en el espacio.', 'Rectas, planos y distancias'],
                ['4', 'Probabilidad', 'Calcular probabilidades en un espacio finito y aplicar probabilidad total y condicionada a hábitos lectores.', 'Probabilidad y sucesos condicionados'],
            ]],
            ['2025-julio-extraordinaria-1', '2025 Julio Extraordinaria', '2024/2025', 'Julio extraordinaria 2025', 'extraordinaria', [
                ['1', 'Álgebra', 'Discutir un sistema lineal con parámetro y trabajar con potencias de una matriz.', 'Sistemas con parámetro y matrices'],
                ['2', 'Análisis', 'Modelizar una parcela con restricciones geométricas y optimizar mediante derivadas.', 'Optimización y derivadas'],
                ['3', 'Geometría', 'Estudiar puntos, rectas, planos, áreas y posiciones relativas en el espacio.', 'Geometría analítica en el espacio'],
                ['4', 'Probabilidad', 'Resolver una normal aplicada a masas corporales y un problema de probabilidad condicionada con lluvia.', 'Normal y probabilidad condicionada'],
            ]],
            ['2024-modelo-2', '2024 modelo', '2023/2024', 'Modelo 2024', 'modelo', [
                ['A/B1', 'Álgebra', 'Resolver un sistema de intérpretes y estudiar matrices, rangos e inversas con parámetro.', 'Sistemas, matrices y rango'],
                ['A/B2', 'Análisis', 'Calcular límites, áreas, dominio, asíntotas, extremos y rectas tangentes.', 'Límites, integrales y funciones racionales'],
                ['A/B3', 'Geometría', 'Trabajar con rectas, planos, tetraedros, ángulos, áreas y volúmenes.', 'Rectas, planos y volúmenes'],
                ['A/B4', 'Probabilidad', 'Resolver problemas de probabilidad condicionada en fútbol y sucesos incompatibles o independientes.', 'Probabilidad condicionada e independencia'],
            ]],
            ['2024-junio-2', '2024 Junio', '2023/2024', 'Junio 2024', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Plantear sistemas de longitudes y estudiar matrices con determinantes, inversas y parámetros.', 'Sistemas y matrices'],
                ['A/B2', 'Análisis', 'Resolver cuestiones de polinomios, integrales, límites trigonométricos y crecimiento.', 'Polinomios, límites e integrales'],
                ['A/B3', 'Geometría', 'Analizar puntos y tetraedros en el espacio con distancias, áreas y volúmenes.', 'Geometría 3D y tetraedros'],
                ['A/B4', 'Probabilidad', 'Calcular probabilidades con operaciones de sucesos y un juego de dados.', 'Sucesos y probabilidad discreta'],
            ]],
            ['2024-julio-extraordinaria-2', '2024 Julio Extraordinaria', '2023/2024', 'Julio extraordinaria 2024', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Discutir un sistema lineal con parámetro y razonar propiedades de determinantes.', 'Sistemas y determinantes'],
                ['A/B2', 'Análisis', 'Construir funciones polinómicas y estudiar la función cúbica x^3 - 3x.', 'Polinomios y análisis de funciones'],
                ['A/B3', 'Geometría', 'Resolver ejercicios de puntos, rectas, planos y posiciones relativas.', 'Rectas, planos y puntos'],
                ['A/B4', 'Probabilidad', 'Trabajar con sucesos incompatibles y un problema de lanzamientos alternos a una diana.', 'Probabilidad y sucesos'],
            ]],
            ['2023-junio', '2023 Junio', '2022/2023', 'Junio 2023', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Modelizar transportes con sistemas y discutir sistemas lineales con parámetro.', 'Sistemas lineales'],
                ['A/B2', 'Análisis', 'Estudiar funciones, derivadas, áreas y funciones definidas a trozos.', 'Derivadas, áreas y continuidad'],
                ['A/B3', 'Geometría', 'Resolver problemas con puntos, rectas, planos, proyecciones y posiciones relativas.', 'Geometría analítica'],
                ['A/B4', 'Probabilidad', 'Calcular probabilidades de sucesos y aplicar una distribución normal a longitudes.', 'Sucesos y distribución normal'],
            ]],
            ['2023-julio-extraordinaria', '2023 Julio Extraordinaria', '2022/2023', 'Julio extraordinaria 2023', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Operar con matrices y discutir un sistema lineal con parámetro.', 'Matrices y sistemas'],
                ['A/B2', 'Análisis', 'Analizar una función de consumo y comparar funciones mediante áreas e intersecciones.', 'Funciones, áreas y optimización'],
                ['A/B3', 'Geometría', 'Trabajar con planos, rectas, puntos, proyecciones y distancias.', 'Planos, rectas y proyecciones'],
                ['A/B4', 'Probabilidad', 'Resolver operaciones de sucesos y una binomial aplicada a una prueba de conducir.', 'Probabilidad y binomial'],
            ]],
            ['2023-modelo', '2023 modelo', '2022/2023', 'Modelo 2023', 'modelo', [
                ['A/B1', 'Álgebra', 'Resolver un reparto de jugadores y estudiar matrices reales con parámetros.', 'Sistemas y matrices'],
                ['A/B2', 'Análisis', 'Calcular derivadas, integrales y optimización de una zona rectangular.', 'Derivadas, integrales y optimización'],
                ['A/B3', 'Geometría', 'Resolver geometría de un depósito, rectas, ángulos y proyecciones.', 'Geometría analítica 3D'],
                ['A/B4', 'Probabilidad', 'Aplicar la distribución normal y calcular probabilidades de sucesos.', 'Normal y sucesos'],
            ]],
            ['2022-modelo-2', '2022 modelo', '2021/2022', 'Modelo 2022', 'modelo', [
                ['A/B1', 'Álgebra', 'Plantear un sistema de alumnos por idioma y estudiar matrices con parámetro.', 'Sistemas, matrices e inversas'],
                ['A/B2', 'Análisis', 'Estudiar continuidad, derivabilidad, extremos y áreas de funciones definidas a trozos.', 'Continuidad, derivabilidad e integrales'],
                ['A/B3', 'Geometría', 'Resolver trayectoria de una sonda, planos perpendiculares, cubos y simetrías.', 'Planos, distancias y simetrías'],
                ['A/B4', 'Probabilidad', 'Calcular probabilidades con urnas y características genéticas independientes.', 'Urnas, independencia y binomial'],
            ]],
            ['2022-junio-3', '2022 Junio', '2021/2022', 'Junio 2022', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Discutir sistemas con parámetro y resolver un reparto proporcional de edades.', 'Sistemas con parámetro'],
                ['A/B2', 'Análisis', 'Estudiar funciones exponenciales o racionales, Bolzano, extremos y áreas.', 'Bolzano, extremos e integrales'],
                ['A/B3', 'Geometría', 'Analizar trayectorias, planos, rectas y distancias en el espacio.', 'Rectas, planos y distancias'],
                ['A/B4', 'Probabilidad', 'Resolver problemas de distribución binomial y probabilidad condicionada con sombreros y pañuelos.', 'Binomial y probabilidad condicionada'],
            ]],
            ['2022-julio-extraordinaria-3', '2022 Julio Extraordinaria', '2021/2022', 'Julio extraordinaria 2022', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Modelizar libros de una biblioteca y estudiar matrices con parámetro.', 'Sistemas y matrices'],
                ['A/B2', 'Análisis', 'Estudiar funciones definidas a trozos, logarítmicas, continuidad y derivabilidad.', 'Funciones a trozos y logaritmos'],
                ['A/B3', 'Geometría', 'Resolver ejercicios con planos, puntos, rectas y posiciones relativas.', 'Planos y rectas'],
                ['A/B4', 'Probabilidad', 'Aplicar binomial y probabilidad total a alumnado y exportación de productos.', 'Binomial y probabilidad total'],
            ]],
            ['2021-modelo', '2021 modelo', '2020/2021', 'Modelo 2021', 'modelo', [
                ['A/B1', 'Álgebra', 'Estudiar matrices, inversas, potencias y sistemas con parámetros.', 'Matrices y sistemas'],
                ['A/B2', 'Análisis', 'Analizar funciones logarítmicas o polinómicas, asíntotas, extremos y áreas.', 'Asíntotas, extremos e integrales'],
                ['A/B3', 'Geometría', 'Resolver ejercicios de puntos, rectas, intersecciones, ángulos y planos.', 'Rectas, planos y ángulos'],
                ['A/B4', 'Probabilidad', 'Trabajar con una binomial de baloncesto y una normal de temperaturas máximas.', 'Binomial y normal'],
            ]],
            ['2021-junio', '2021 Junio', '2020/2021', 'Junio 2021', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Resolver sistemas aplicados a acciones y discutir sistemas dependientes de parámetro.', 'Sistemas lineales'],
                ['A/B2', 'Análisis', 'Calcular áreas entre parábolas y estudiar funciones trigonométricas o exponenciales.', 'Áreas, continuidad y derivabilidad'],
                ['A/B3', 'Geometría', 'Trabajar con rectas, planos, intersecciones y relaciones de perpendicularidad.', 'Rectas y planos'],
                ['A/B4', 'Probabilidad', 'Aplicar una normal a vida animal y probabilidad condicionada a mediciones de calidad del aire.', 'Normal y probabilidad condicionada'],
            ]],
            ['2021-julio-extraordinaria', '2021 Julio Extraordinaria', '2020/2021', 'Julio extraordinaria 2021', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Modelizar seguidores en redes sociales y construir sistemas lineales.', 'Sistemas lineales'],
                ['A/B2', 'Análisis', 'Calcular límites, continuidad, derivabilidad, extremos y áreas.', 'Límites, derivadas e integrales'],
                ['A/B3', 'Geometría', 'Resolver problemas de punto, recta, plano, ángulos y posiciones relativas.', 'Geometría analítica'],
                ['A/B4', 'Probabilidad', 'Resolver una urna con reposición parcial y una binomial aplicada a lluvia.', 'Urnas y binomial'],
            ]],
            ['2020-modelo', '2020 modelo', '2019/2020', 'Modelo 2020', 'modelo', [
                ['A/B1', 'Álgebra', 'Resolver una mezcla de gases para un invernadero y un sistema matricial con parámetro.', 'Sistemas, mezclas y matrices'],
                ['A/B2', 'Análisis', 'Estudiar exponenciales y funciones racionales con tangentes, límites, áreas y asíntotas.', 'Tangentes, límites e integrales'],
                ['A/B3', 'Geometría', 'Analizar rectas, planos, distancias, áreas y ángulos en el espacio.', 'Rectas, planos y distancias'],
                ['A/B4', 'Probabilidad', 'Calcular probabilidades de sucesos y aplicar una normal a temperaturas máximas.', 'Sucesos y distribución normal'],
            ]],
            ['2020-junio', '2020 Junio', '2019/2020', 'Junio 2020', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Discutir sistemas con parámetro y resolver un sistema aplicado a precios de pescado.', 'Sistemas lineales'],
                ['A/B2', 'Análisis', 'Aplicar teoremas de funciones, rectas tangentes, integrales y funciones a trozos.', 'Teoremas, derivadas e integrales'],
                ['A/B3', 'Geometría', 'Estudiar rectas, planos, posiciones relativas y proyecciones.', 'Rectas y planos'],
                ['A/B4', 'Probabilidad', 'Resolver probabilidades de tiro con arco, sucesos e independencia.', 'Probabilidad, binomial e independencia'],
            ]],
            ['2020-septiembre', '2020 Septiembre', '2019/2020', 'Septiembre 2020', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Construir matrices con condiciones de rango y operar con matrices e inversas.', 'Matrices, rango e inversas'],
                ['A/B2', 'Análisis', 'Estudiar continuidad, derivabilidad, crecimiento y optimización de potencia.', 'Continuidad, derivadas y optimización'],
                ['A/B3', 'Geometría', 'Resolver ejercicios con punto, recta, paralelogramo, planos y distancias.', 'Geometría analítica'],
                ['A/B4', 'Probabilidad', 'Aplicar probabilidad total con urnas y estudiar sucesos independientes.', 'Urnas e independencia'],
            ]],
            ['2019-modelo', '2019 modelo', '2018/2019', 'Modelo 2019', 'modelo', [
                ['A/B1', 'Álgebra', 'Construir matrices 3x3 con condiciones de rango y discutir sistemas lineales con parámetro.', 'Matrices, rango y sistemas'],
                ['A/B2', 'Análisis', 'Modelizar contaminación con una función, leer una gráfica y calcular integrales.', 'Funciones, gráficas e integrales'],
                ['A/B3', 'Geometría', 'Calcular planos, distancias y posiciones relativas de rectas en el espacio.', 'Planos, rectas y distancias'],
                ['A/B4', 'Probabilidad', 'Aplicar aproximación normal a una binomial y probabilidad condicionada en un grupo de idiomas.', 'Normal, binomial y probabilidad condicionada'],
            ], false],
            ['2019-junio', '2019 Junio', '2018/2019', 'Junio 2019', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Estudiar el rango de una matriz con parámetro y resolver un sistema aplicado a precios de cafetería.', 'Matrices, rango y sistemas'],
                ['A/B2', 'Análisis', 'Analizar funciones con asíntotas, tangentes, crecimiento y límites laterales.', 'Asíntotas, derivadas y límites'],
                ['A/B3', 'Geometría', 'Calcular distancias de puntos a planos, puntos más próximos y posiciones relativas.', 'Planos, distancias y geometría 3D'],
                ['A/B4', 'Probabilidad', 'Resolver probabilidad total y condicionada en un estudio farmacéutico.', 'Probabilidad total y condicionada'],
            ]],
            ['2019-julio-extraordinaria', '2019 Julio Extraordinaria', '2018/2019', 'Julio extraordinaria 2019', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Discutir sistemas lineales y operar con matrices 2x2 con parámetro.', 'Sistemas, matrices y parámetros'],
                ['A/B2', 'Análisis', 'Usar regla de la cadena, integrales y modelos de propagación mediante derivadas.', 'Derivadas, regla de la cadena e integrales'],
                ['A/B3', 'Geometría', 'Resolver planos por tres puntos, simetrías y rectas paralelas a planos.', 'Planos, rectas y simetrías'],
                ['A/B4', 'Probabilidad', 'Aplicar binomial y probabilidad condicionada a selección de personal y defectos de vehículos.', 'Binomial y probabilidad condicionada'],
            ]],
            ['2018-julio-extraordinaria', '2018 Julio Extraordinaria', '2017/2018', 'Julio extraordinaria 2018', 'extraordinaria', [
                ['A/B1', 'Álgebra', 'Discutir matrices y sistemas, y plantear un sistema aplicado a viajes por países.', 'Matrices y sistemas lineales'],
                ['A/B2', 'Análisis', 'Estudiar continuidad, asíntotas, áreas y la interpretación de una gráfica.', 'Continuidad, asíntotas e integrales'],
                ['A/B3', 'Geometría', 'Calcular planos, distancias y relaciones entre puntos y rectas.', 'Planos, rectas y distancias'],
                ['A/B4', 'Probabilidad', 'Trabajar con una distribución normal y con probabilidad condicionada.', 'Distribución normal y probabilidad'],
            ]],
            ['2018-junio-ordinaria', '2018 Junio Ordinaria', '2017/2018', 'Junio ordinaria 2018', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Discutir sistemas con parámetro y estudiar matrices invertibles.', 'Sistemas, matrices e inversas'],
                ['A/B2', 'Análisis', 'Aplicar el valor medio, estudiar asíntotas y calcular áreas de funciones racionales.', 'Valor medio, asíntotas e integrales'],
                ['A/B3', 'Geometría', 'Calcular distancias, posiciones relativas y rectas en el espacio.', 'Rectas, planos y distancias'],
                ['A/B4', 'Probabilidad', 'Resolver probabilidad condicionada y control de calidad en productos.', 'Probabilidad condicionada'],
            ]],
            ['2018-modelo', '2018 modelo', '2017/2018', 'Modelo 2018', 'modelo', [
                ['A/B1', 'Álgebra', 'Estudiar matrices, inversas y un sistema lineal AX = B dependiente de parámetro.', 'Matrices y sistemas con parámetro'],
                ['A/B2', 'Análisis', 'Resolver tangentes, áreas, asíntotas y optimización sobre funciones.', 'Tangentes, integrales y asíntotas'],
                ['A/B3', 'Geometría', 'Resolver puntos equidistantes, rectas contenidas en planos y ángulos entre planos.', 'Planos, rectas y ángulos'],
                ['A/B4', 'Probabilidad', 'Aplicar una normal a pesos de estudiantes y probabilidad con extracciones sucesivas.', 'Normal y extracciones'],
            ]],
            ['2017-junio', '2017 Junio', '2016/2017', 'Junio 2017', 'ordinaria', [
                ['A/B1', 'Álgebra', 'Discutir un sistema de ecuaciones con parámetro y operar con matrices diagonalizables.', 'Sistemas y matrices'],
                ['A/B2', 'Análisis', 'Calcular límites, rectas tangentes, áreas y estudiar funciones.', 'Límites, derivadas e integrales'],
                ['A/B3', 'Geometría', 'Estudiar rectas por puntos, posiciones relativas y áreas de triángulos.', 'Rectas y geometría vectorial'],
                ['A/B4', 'Probabilidad', 'Resolver probabilidad total y condicionada en hábitos de ocio.', 'Probabilidad total y condicionada'],
            ]],
            ['2017-modelo', '2017 modelo', '2016/2017', 'Modelo 2017', 'modelo', [
                ['A/B1', 'Geometría y álgebra', 'Calcular distancia entre rectas que se cruzan y estudiar rangos de matrices.', 'Rectas, matrices y rango'],
                ['A/B2', 'Análisis', 'Optimizar ingresos de una rifa, estudiar exponenciales y calcular áreas.', 'Optimización, exponenciales e integrales'],
                ['A/B3', 'Álgebra y geometría', 'Resolver un sistema de flores y distancias de puntos a rectas.', 'Sistemas, puntos y rectas'],
                ['A/B4', 'Probabilidad', 'Aplicar probabilidad total y condicionada a población animal y sucesos.', 'Probabilidad condicionada'],
            ]],
            ['2017-septiembre', '2017 Septiembre', '2016/2017', 'Septiembre 2017', 'extraordinaria', [
                ['A/B1', 'Análisis y álgebra', 'Estudiar continuidad y derivabilidad de funciones a trozos y calcular rangos de matrices.', 'Funciones a trozos, matrices y rango'],
                ['A/B2', 'Geometría', 'Calcular distancias entre rectas y planos que contienen puntos o rectas.', 'Rectas, planos y distancias'],
                ['A/B3', 'Álgebra aplicada', 'Resolver mezclas de aleaciones y operaciones matriciales.', 'Sistemas y matrices'],
                ['A/B4', 'Geometría', 'Calcular distancias de puntos a rectas y ángulos de triángulos.', 'Geometría analítica'],
            ]],
            ['2016-septiembre', '2016 Septiembre', '2015/2016', 'Septiembre 2016', 'extraordinaria', [
                ['A/B1', 'Análisis y álgebra', 'Estudiar una función exponencial y discutir un sistema lineal con parámetro.', 'Funciones exponenciales y sistemas'],
                ['A/B2', 'Geometría y análisis', 'Resolver distancias entre rectas y estudiar funciones a trozos.', 'Rectas, distancias y funciones'],
                ['A/B3', 'Álgebra y geometría', 'Resolver problemas de determinantes y calcular volúmenes de tetraedros.', 'Determinantes y volúmenes'],
                ['A/B4', 'Geometría', 'Calcular puntos de un plano más cercanos al origen y posiciones relativas.', 'Planos y distancias'],
            ]],
            ['2016-junio', '2016 Junio', '2015/2016', 'Junio 2016', 'ordinaria', [
                ['A/B1', 'Análisis y álgebra', 'Estudiar continuidad de funciones logarítmicas y discutir sistemas con parámetro.', 'Continuidad y sistemas lineales'],
                ['A/B2', 'Álgebra y geometría', 'Resolver ecuaciones matriciales y calcular áreas de paralelogramos.', 'Matrices y geometría vectorial'],
                ['A/B3', 'Análisis', 'Determinar polinomios mediante derivadas e integrales.', 'Derivadas e integrales'],
                ['A/B4', 'Probabilidad', 'Resolver cuestiones de probabilidad y distribuciones asociadas.', 'Probabilidad'],
            ]],
            ['2016-modelo', '2016 modelo', '2015/2016', 'Modelo 2016', 'modelo', [
                ['A/B1', 'Álgebra y geometría', 'Discutir sistemas lineales y resolver problemas de planos.', 'Sistemas, planos y vectores'],
                ['A/B2', 'Análisis', 'Estudiar extremos, pendientes máximas e integrales de funciones.', 'Extremos, derivadas e integrales'],
                ['A/B3', 'Álgebra', 'Resolver sistemas matriciales con valores propios y parámetros.', 'Matrices, parámetros y sistemas'],
                ['A/B4', 'Probabilidad', 'Trabajar con probabilidad de sucesos y distribuciones.', 'Probabilidad'],
            ]],
            ['2015-junio', '2015 Junio', '2014/2015', 'Junio 2015', 'ordinaria', [
                ['A/B1', 'Análisis y geometría', 'Estudiar una función logarítmica y resolver proyecciones de puntos sobre rectas.', 'Logaritmos, tangentes y geometría'],
                ['A/B2', 'Geometría y análisis', 'Calcular volúmenes vectoriales, derivabilidad e integrales.', 'Vectores, derivadas e integrales'],
                ['A/B3', 'Álgebra', 'Resolver ecuaciones matriciales y trabajar con determinantes.', 'Matrices y determinantes'],
                ['A/B4', 'Álgebra', 'Calcular valores de parámetros en matrices y sistemas.', 'Parámetros y sistemas'],
            ]],
            ['2015-modelo', '2015 modelo', '2014/2015', 'Modelo 2015', 'modelo', [
                ['A/B1', 'Álgebra y geometría', 'Estudiar rangos de matrices con parámetro y distancias entre rectas.', 'Matrices, rango y rectas'],
                ['A/B2', 'Análisis', 'Resolver crecimiento, decrecimiento, áreas, límites e integrales.', 'Crecimiento, límites e integrales'],
                ['A/B3', 'Álgebra', 'Resolver sistemas matriciales y matrices invertibles.', 'Sistemas matriciales'],
                ['A/B4', 'Álgebra', 'Discutir sistemas lineales y casos compatibles indeterminados.', 'Sistemas lineales'],
            ]],
            ['2015-septiembre', '2015 Septiembre', '2014/2015', 'Septiembre 2015', 'extraordinaria', [
                ['A/B1', 'Álgebra y análisis', 'Discutir un sistema lineal con parámetro y estudiar continuidad de una función a trozos.', 'Sistemas y funciones a trozos'],
                ['A/B2', 'Geometría', 'Calcular distancias entre rectas, planos y proyecciones de puntos.', 'Rectas, planos y distancias'],
                ['A/B3', 'Álgebra', 'Aplicar propiedades de determinantes y resolver cálculos matriciales.', 'Determinantes y matrices'],
                ['A/B4', 'Probabilidad', 'Resolver ejercicios de probabilidad dentro de la opción correspondiente.', 'Probabilidad'],
            ]],
        ];
        $entries = array_merge($entries, $this->madridMatematicasHistoricEntries());

        $pages = [];
        foreach ($entries as $entry) {
            [$slug, $name, $course, $call, $kind, $questions] = $entry;

            $pages['selectividad/madrid/matematicas/' . $slug] = $this->madridMatematicasPage(
                $slug,
                $name,
                $course,
                $call,
                $kind,
                $questions,
                $entry[6] ?? true
            );
        }

        return $pages;
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string, 4: string, 5: list<array{0: string, 1: string, 2: string, 3: string}>, 6?: bool}>
     */
    private function madridMatematicasHistoricEntries(): array
    {
        $rows = [
            ['2014-septiembre', '2014 Septiembre', '2013/2014', 'Septiembre 2014', 'extraordinaria'],
            ['2014-junio', '2014 Junio', '2013/2014', 'Junio 2014', 'ordinaria'],
            ['2014-modelo', '2014 modelo', '2013/2014', 'Modelo 2014', 'modelo'],
            ['2013-septiembre', '2013 Septiembre', '2012/2013', 'Septiembre 2013', 'extraordinaria'],
            ['2013-junio', '2013 Junio', '2012/2013', 'Junio 2013', 'ordinaria'],
            ['2013-modelo', '2013 modelo', '2012/2013', 'Modelo 2013', 'modelo'],
            ['2012-septiembre', '2012 Septiembre', '2011/2012', 'Septiembre 2012', 'extraordinaria'],
            ['2012-junio', '2012 Junio', '2011/2012', 'Junio 2012', 'ordinaria'],
            ['2012-modelo', '2012 modelo', '2011/2012', 'Modelo 2012', 'modelo'],
            ['2011-modelo', '2011 modelo', '2010/2011', 'Modelo 2011', 'modelo'],
            ['2011-septiembre', '2011 Septiembre', '2010/2011', 'Septiembre 2011', 'extraordinaria'],
            ['2011-junio', '2011 Junio', '2010/2011', 'Junio 2011', 'ordinaria'],
            ['2010-septiembre-f-m', '2010 Septiembre - F.M.', '2009/2010', 'Septiembre F.M. 2010', 'extraordinaria'],
            ['2010-modelo', '2010 modelo', '2009/2010', 'Modelo 2010', 'modelo'],
            ['2010-septiembre-f-g', '2010 Septiembre - F.G.', '2009/2010', 'Septiembre F.G. 2010', 'extraordinaria'],
            ['2010-junio-f-m', '2010 Junio - F.M.', '2009/2010', 'Junio F.M. 2010', 'ordinaria'],
            ['2010-junio-f-g', '2010 Junio - F.G.', '2009/2010', 'Junio F.G. 2010', 'ordinaria'],
            ['2009-junio', '2009 Junio', '2008/2009', 'Junio 2009', 'ordinaria'],
            ['2009-modelo', '2009 modelo', '2008/2009', 'Modelo 2009', 'modelo'],
            ['2009-septiembre', '2009 Septiembre', '2008/2009', 'Septiembre 2009', 'extraordinaria'],
            ['2008-septiembre', '2008 Septiembre', '2007/2008', 'Septiembre 2008', 'extraordinaria'],
            ['2008-junio', '2008 Junio', '2007/2008', 'Junio 2008', 'ordinaria'],
            ['2008-modelo', '2008 modelo', '2007/2008', 'Modelo 2008', 'modelo'],
            ['2007-septiembre', '2007 Septiembre', '2006/2007', 'Septiembre 2007', 'extraordinaria'],
            ['2007-modelo', '2007 modelo', '2006/2007', 'Modelo 2007', 'modelo'],
            ['2007-junio', '2007 Junio', '2006/2007', 'Junio 2007', 'ordinaria'],
            ['2006-junio', '2006 Junio', '2005/2006', 'Junio 2006', 'ordinaria'],
            ['2006-modelo', '2006 modelo', '2005/2006', 'Modelo 2006', 'modelo'],
            ['2006-septiembre', '2006 Septiembre', '2005/2006', 'Septiembre 2006', 'extraordinaria'],
            ['2005-modelo', '2005 modelo', '2004/2005', 'Modelo 2005', 'modelo'],
            ['2005-septiembre', '2005 Septiembre', '2004/2005', 'Septiembre 2005', 'extraordinaria'],
            ['2005-junio', '2005 Junio', '2004/2005', 'Junio 2005', 'ordinaria'],
            ['2004-modelo', '2004 modelo', '2003/2004', 'Modelo 2004', 'modelo'],
            ['2004-septiembre', '2004 Septiembre', '2003/2004', 'Septiembre 2004', 'extraordinaria'],
            ['2004-junio', '2004 Junio', '2003/2004', 'Junio 2004', 'ordinaria'],
            ['2003-septiembre', '2003 Septiembre', '2002/2003', 'Septiembre 2003', 'extraordinaria'],
            ['2003-junio', '2003 Junio', '2002/2003', 'Junio 2003', 'ordinaria'],
            ['2002-septiembre', '2002 Septiembre', '2001/2002', 'Septiembre 2002', 'extraordinaria'],
            ['2002-junio', '2002 Junio', '2001/2002', 'Junio 2002', 'ordinaria'],
            ['2001-junio', '2001 Junio', '2000/2001', 'Junio 2001', 'ordinaria'],
            ['2001-septiembre', '2001 Septiembre', '2000/2001', 'Septiembre 2001', 'extraordinaria'],
            ['2000-septiembre', '2000 Septiembre', '1999/2000', 'Septiembre 2000', 'extraordinaria'],
            ['2000-junio', '2000 Junio', '1999/2000', 'Junio 2000', 'ordinaria'],
            ['1999-septiembre', '1999 Septiembre', '1998/1999', 'Septiembre 1999', 'extraordinaria'],
            ['1999-junio', '1999 Junio', '1998/1999', 'Junio 1999', 'ordinaria', false],
            ['1998-junio', '1998 Junio', '1997/1998', 'Junio 1998', 'ordinaria', false],
            ['1998-septiembre', '1998 Septiembre', '1997/1998', 'Septiembre 1998', 'extraordinaria', false],
            ['1997-septiembre', '1997 Septiembre', '1996/1997', 'Septiembre 1997', 'extraordinaria', false],
            ['1997-junio', '1997 Junio', '1996/1997', 'Junio 1997', 'ordinaria'],
            ['1996-septiembre', '1996 Septiembre', '1995/1996', 'Septiembre 1996', 'extraordinaria'],
            ['1996-junio', '1996 Junio', '1995/1996', 'Junio 1996', 'ordinaria', false],
            ['1995-septiembre', '1995 Septiembre', '1994/1995', 'Septiembre 1995', 'extraordinaria', false],
            ['1995-junio', '1995 Junio', '1994/1995', 'Junio 1995', 'ordinaria'],
            ['1994-septiembre', '1994 Septiembre', '1993/1994', 'Septiembre 1994', 'extraordinaria'],
            ['1994-junio', '1994 Junio', '1993/1994', 'Junio 1994', 'ordinaria'],
        ];

        return array_map(
            fn (array $row): array => [
                $row[0],
                $row[1],
                $row[2],
                $row[3],
                $row[4],
                $this->madridMatematicasHistoricQuestions(),
                $row[5] ?? true,
            ],
            $rows
        );
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function madridMatematicasHistoricQuestions(): array
    {
        return [
            ['A/B1', 'Álgebra', 'Resolver ejercicios de sistemas lineales, matrices, determinantes o discusión con parámetros.', 'Sistemas, matrices y determinantes'],
            ['A/B2', 'Análisis', 'Estudiar funciones, límites, derivadas, asíntotas, tangentes, áreas o integrales.', 'Funciones, derivadas e integrales'],
            ['A/B3', 'Geometría', 'Trabajar con rectas, planos, puntos, distancias, ángulos, áreas o volúmenes en el espacio.', 'Geometría analítica en el espacio'],
            ['A/B4', 'Cierre de opción', 'Completar la opción con el ejercicio final de álgebra, análisis, geometría o probabilidad según la convocatoria.', 'Problemas finales de opción'],
        ];
    }

    /**
     * @param list<array{0: string, 1: string, 2: string, 3: string}> $questions
     *
     * @return array<string, mixed>
     */
    private function madridMatematicasPage(
        string $slug,
        string $name,
        string $course,
        string $call,
        string $kind,
        array $questions,
        bool $hasSolution
    ): array {
        $topicLabels = array_values(array_unique(array_map(static fn (array $question): string => $question[3], $questions)));
        $questionsTitle = 'Preguntas de PAU Madrid ' . $name . ' de Matemáticas II';
        $topicSummary = implode(', ', array_slice($topicLabels, 0, 4));
        $callLower = mb_strtolower($call);
        $titleEnding = $hasSolution ? 'enunciado, temas y solución' : 'enunciado y temas';
        $metaTitleEnding = $hasSolution ? 'Enunciado y solución' : 'Enunciado y temas';

        return [
            'metaTitle' => 'PAU Madrid ' . $name . ' Matemáticas II | ' . $metaTitleEnding,
            'title' => 'PAU Madrid ' . $name . ' Matemáticas II: ' . $titleEnding,
            'metaDescription' => $hasSolution
                ? 'Consulta el examen PAU/EvAU Madrid ' . $name . ' de Matemáticas II: enunciado, datos del examen, bloques, temas, dificultad y acceso a la solución.'
                : 'Consulta el examen PAU/EvAU Madrid ' . $name . ' de Matemáticas II: enunciado, datos del examen, bloques, temas y dificultad.',
            'summaryTitle' => 'Resumen del examen',
            'summaryParagraphs' => [
                'Este examen PAU/EvAU de Matemáticas II de Madrid corresponde a ' . $callLower . ' del curso ' . $course . '. Reúne ejercicios de ' . $topicSummary . ', con la estructura habitual de álgebra, análisis y geometría, y probabilidad en las convocatorias donde aparece.',
                'La prueba dura 90 minutos y reparte la puntuación entre los ejercicios indicados en el enunciado oficial. En esta página puedes abrir el enunciado, revisar los temas de cada bloque y acceder al pack PAU de Matemáticas II Madrid.',
            ],
            'solutionCta' => [
                'eyebrow' => $hasSolution ? 'Solución de ' . $call : 'Pack PAU Matemáticas II Madrid',
                'title' => $hasSolution ? 'Corrige este examen con la solución completa' : 'Practica este examen con el pack completo',
                'text' => $hasSolution
                    ? 'El pack PAU {subjectName} {communityName} incluye la solución de este examen y el histórico {yearRange} de enunciados y soluciones para practicar sin buscar año por año.'
                    : 'El pack PAU {subjectName} {communityName} reúne el histórico {yearRange} de enunciados y soluciones disponibles para practicar sin buscar año por año.',
                'priceText' => 'Pago único de {formattedPrice}, sin suscripción.',
                'buttonLabel' => $hasSolution ? 'Ver solución y pack completo' : 'Ver pack completo',
                'eventLabel' => 'exam-main-solution-cta-seo-madrid-math-' . $slug,
            ],
            'statementTitle' => $hasSolution ? 'Enunciado y solución' : 'Enunciado oficial',
            'visibleFileLabel' => 'Ver enunciado oficial de ' . $call,
            'visibleFileTitle' => 'Ver el enunciado de PAU Madrid ' . $name . ' de Matemáticas II',
            'lockedFileLabel' => 'Ver solución completa en el pack PAU {subjectName} {communityName}',
            'premiumFileLabel' => 'Ver {fileName} con Premium',
            'examDataTitle' => 'Datos del examen',
            'examData' => [
                ['label' => 'Prueba', 'value' => 'PAU/EvAU Madrid'],
                ['label' => 'Asignatura', 'value' => 'Matemáticas II'],
                ['label' => 'Curso', 'value' => $course],
                ['label' => 'Convocatoria', 'value' => $call],
                ['label' => 'Duración', 'value' => '90 minutos'],
                ['label' => 'Calificación', 'value' => 'Consulta la puntuación de cada ejercicio en el enunciado oficial'],
                ['label' => 'Dificultad estimada', 'value' => '{difficulty}/10'],
            ],
            'questionsTitle' => $questionsTitle,
            'questions' => array_map(static fn (array $question): array => [
                'block' => $question[0],
                'question' => $question[1],
                'task' => $question[2],
                'topic' => $question[3],
            ], $questions),
            'topicsTitle' => 'Temas que aparecen',
            'topics' => array_map(static fn (string $topic): string => $topic . '.', $topicLabels),
            'practiceTitle' => 'Cómo practicar este examen',
            'practiceSteps' => [
                'Haz una selección de preguntas respetando el límite de 90 minutos.',
                'Comprueba si los fallos son de planteamiento, cálculo o interpretación del enunciado.',
                'Corrige el examen con la solución y repite los bloques donde pierdas más puntuación.',
                'Refuerza los mismos temas con otros exámenes de Matemáticas II de Madrid.',
            ],
            'relatedTitle' => 'Exámenes relacionados',
            'relatedExams' => $this->madridMatematicasRelatedExams($slug),
            'quickFactsTitle' => 'Ficha rápida',
            'quickFacts' => [
                'Comunidad: Madrid',
                'Asignatura: Matemáticas II',
                'Convocatoria: ' . $call,
                'Nivel: 2º Bachillerato',
                'Tiempo: 90 minutos',
            ],
            'educationalLevel' => '2º Bachillerato',
            'learningResourceTypes' => $hasSolution
                ? ['Examen PAU', 'Examen de selectividad', 'Solución de examen']
                : ['Examen PAU', 'Examen de selectividad', 'Enunciado de examen'],
            'schemaAbout' => array_merge([
                'PAU Madrid',
                'EvAU Madrid',
                'Matemáticas II',
            ], $topicLabels),
            'analyticsLabel' => 'exam-seo-madrid-math-' . $slug,
            'sidebarPackEventLabel' => 'exam-sidebar-seo-madrid-math-' . $slug,
            'authorName' => 'Juan Carlos Rojo',
            'authorJobTitle' => 'Profesor de apoyo especializado en PAU, Bachillerato y ESO',
        ];
    }

    /**
     * @return list<array{label: string, type: string, examSlug?: string}>
     */
    private function madridMatematicasRelatedExams(string $currentSlug): array
    {
        $relatedExams = [
            ['label' => 'Todos los exámenes de PAU Matemáticas II Madrid', 'type' => 'subject'],
        ];

        foreach ([
            ['label' => 'Modelo PAU Madrid 2025 Matemáticas II', 'type' => 'exam', 'examSlug' => '2025-modelo'],
            ['label' => 'PAU Madrid 2025 Matemáticas II junio', 'type' => 'exam', 'examSlug' => '2025-junio-1'],
            ['label' => 'PAU Madrid 2024 Matemáticas II junio', 'type' => 'exam', 'examSlug' => '2024-junio-2'],
        ] as $candidate) {
            if ($candidate['examSlug'] !== $currentSlug) {
                $relatedExams[] = $candidate;
            }

            if (\count($relatedExams) === 3) {
                break;
            }
        }

        return $relatedExams;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function madridFisicaPages(): array
    {
        $entries = [
            ['2025-junio-2', '2025 Junio', '2024/2025', 'Junio 2025', 'ordinaria', $this->madridFisicaPau2025Questions()],
            ['2024-julio-extraordinaria-3', '2024 Julio Extraordinaria', '2023/2024', 'Julio extraordinaria 2024', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2024-junio-3', '2024 Junio', '2023/2024', 'Junio 2024', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2024-modelo-3', '2024 modelo', '2023/2024', 'Modelo 2024', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2023-modelo-1', '2023 modelo', '2022/2023', 'Modelo 2023', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2023-junio-1', '2023 Junio', '2022/2023', 'Junio 2023', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2023-julio-extraordinaria-1', '2023 Julio Extraordinaria', '2022/2023', 'Julio extraordinaria 2023', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2022-junio', '2022 Junio', '2021/2022', 'Junio 2022', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2022-modelo-1', '2022 modelo', '2021/2022', 'Modelo 2022', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2022-julio-extraordinaria', '2022 Julio Extraordinaria', '2021/2022', 'Julio extraordinaria 2022', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2021-junio', '2021 Junio', '2020/2021', 'Junio 2021', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2021-modelo', '2021 modelo', '2020/2021', 'Modelo 2021', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2021-julio-extraordinaria', '2021 Julio Extraordinaria', '2020/2021', 'Julio extraordinaria 2021', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2020-septiembre', '2020 Septiembre', '2019/2020', 'Septiembre 2020', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2020-junio', '2020 Junio', '2019/2020', 'Junio 2020', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2020-modelo', '2020 modelo', '2019/2020', 'Modelo 2020', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2019-modelo', '2019 modelo', '2018/2019', 'Modelo 2019', 'modelo', $this->madridFisicaClassicQuestions(), false],
            ['2019-julio-extraordinaria', '2019 Julio Extraordinaria', '2018/2019', 'Julio extraordinaria 2019', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2019-junio', '2019 Junio', '2018/2019', 'Junio 2019', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2018-modelo', '2018 modelo', '2017/2018', 'Modelo 2018', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2018-julio-extraordinaria', '2018 Julio Extraordinaria', '2017/2018', 'Julio extraordinaria 2018', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2018-junio-ordinaria', '2018 Junio Ordinaria', '2017/2018', 'Junio ordinaria 2018', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2017-septiembre', '2017 Septiembre', '2016/2017', 'Septiembre 2017', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2017-junio', '2017 Junio', '2016/2017', 'Junio 2017', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2016-modelo', '2016 modelo', '2015/2016', 'Modelo 2016', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2016-septiembre', '2016 Septiembre', '2015/2016', 'Septiembre 2016', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2016-junio', '2016 Junio', '2015/2016', 'Junio 2016', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2015-septiembre', '2015 Septiembre', '2014/2015', 'Septiembre 2015', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2015-junio', '2015 Junio', '2014/2015', 'Junio 2015', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2015-modelo', '2015 modelo', '2014/2015', 'Modelo 2015', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2014-junio', '2014 Junio', '2013/2014', 'Junio 2014', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2014-modelo', '2014 modelo', '2013/2014', 'Modelo 2014', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2014-septiembre', '2014 Septiembre', '2013/2014', 'Septiembre 2014', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2013-septiembre', '2013 Septiembre', '2012/2013', 'Septiembre 2013', 'extraordinaria', $this->madridFisicaClassicQuestions()],
            ['2013-junio', '2013 Junio', '2012/2013', 'Junio 2013', 'ordinaria', $this->madridFisicaClassicQuestions()],
            ['2013-modelo', '2013 modelo', '2012/2013', 'Modelo 2013', 'modelo', $this->madridFisicaClassicQuestions()],
            ['2012-modelo', '2012 modelo', '2011/2012', 'Modelo 2012', 'modelo', $this->madridFisicaQuestionProblemQuestions()],
            ['2012-junio', '2012 Junio', '2011/2012', 'Junio 2012', 'ordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2012-septiembre', '2012 Septiembre', '2011/2012', 'Septiembre 2012', 'extraordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2011-modelo', '2011 modelo', '2010/2011', 'Modelo 2011', 'modelo', $this->madridFisicaQuestionProblemQuestions()],
            ['2011-junio', '2011 Junio', '2010/2011', 'Junio 2011', 'ordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2010-modelo', '2010 modelo', '2009/2010', 'Modelo 2010', 'modelo', $this->madridFisicaQuestionProblemQuestions()],
            ['2010-junio-f-g', '2010 Junio - F.G.', '2009/2010', 'Junio F.G. 2010', 'ordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2010-junio-f-m', '2010 Junio - F.M.', '2009/2010', 'Junio F.M. 2010', 'ordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2010-septiembre-f-g', '2010 Septiembre - F.G.', '2009/2010', 'Septiembre F.G. 2010', 'extraordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2010-septiembre-f-m', '2010 Septiembre - F.M.', '2009/2010', 'Septiembre F.M. 2010', 'extraordinaria', $this->madridFisicaQuestionProblemQuestions()],
            ['2009-modelo', '2009 modelo', '2008/2009', 'Modelo 2009', 'modelo', $this->madridFisicaLogseQuestions()],
            ['2009-junio', '2009 Junio', '2008/2009', 'Junio 2009', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2009-septiembre', '2009 Septiembre', '2008/2009', 'Septiembre 2009', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2008-modelo', '2008 modelo', '2007/2008', 'Modelo 2008', 'modelo', $this->madridFisicaLogseQuestions()],
            ['2008-junio', '2008 Junio', '2007/2008', 'Junio 2008', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2008-septiembre', '2008 Septiembre', '2007/2008', 'Septiembre 2008', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2007-modelo', '2007 modelo', '2006/2007', 'Modelo 2007', 'modelo', $this->madridFisicaLogseQuestions()],
            ['2007-junio', '2007 Junio', '2006/2007', 'Junio 2007', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2007-septiembre', '2007 Septiembre', '2006/2007', 'Septiembre 2007', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2006-modelo', '2006 modelo', '2005/2006', 'Modelo 2006', 'modelo', $this->madridFisicaLogseQuestions()],
            ['2006-junio', '2006 Junio', '2005/2006', 'Junio 2006', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2006-septiembre', '2006 Septiembre', '2005/2006', 'Septiembre 2006', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2005-modelo', '2005 modelo', '2004/2005', 'Modelo 2005', 'modelo', $this->madridFisicaLogseQuestions()],
            ['2005-junio', '2005 Junio', '2004/2005', 'Junio 2005', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2005-septiembre', '2005 Septiembre', '2004/2005', 'Septiembre 2005', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2004-modelo', '2004 modelo', '2003/2004', 'Modelo 2004', 'modelo', $this->madridFisicaLogseQuestions()],
            ['2004-junio', '2004 Junio', '2003/2004', 'Junio 2004', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2004-septiembre', '2004 Septiembre', '2003/2004', 'Septiembre 2004', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2003-junio', '2003 Junio', '2002/2003', 'Junio 2003', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2003-septiembre', '2003 Septiembre', '2002/2003', 'Septiembre 2003', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2002-junio', '2002 Junio', '2001/2002', 'Junio 2002', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2002-septiembre', '2002 Septiembre', '2001/2002', 'Septiembre 2002', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2001-junio', '2001 Junio', '2000/2001', 'Junio 2001', 'ordinaria', $this->madridFisicaLogseQuestions()],
            ['2000-septiembre', '2000 Septiembre', '1999/2000', 'Septiembre 2000', 'extraordinaria', $this->madridFisicaLogseQuestions()],
            ['2000-junio', '2000 Junio', '1999/2000', 'Junio 2000', 'ordinaria', $this->madridFisicaLogseQuestions(), false],
            ['1999-junio', '1999 Junio', '1998/1999', 'Junio 1999', 'ordinaria', $this->madridFisicaLogseQuestions(), false],
            ['1999-septiembre', '1999 Septiembre', '1998/1999', 'Septiembre 1999', 'extraordinaria', $this->madridFisicaLogseQuestions(), false],
            ['1998-junio', '1998 Junio', '1997/1998', 'Junio 1998', 'ordinaria', $this->madridFisicaLogseQuestions(), false],
            ['1997-junio', '1997 Junio', '1996/1997', 'Junio 1997', 'ordinaria', $this->madridFisicaLogseQuestions(), false],
            ['1996-junio', '1996 Junio', '1995/1996', 'Junio 1996', 'ordinaria', $this->madridFisicaLogseQuestions(), false],
            ['1996-septiembre', '1996 Septiembre', '1995/1996', 'Septiembre 1996', 'extraordinaria', $this->madridFisicaLogseQuestions(), false],
        ];

        $pages = [];
        foreach ($entries as $entry) {
            [$slug, $name, $course, $call, $kind, $questions] = $entry;

            $pages['selectividad/madrid/fisica/' . $slug] = $this->madridFisicaPage(
                $slug,
                $name,
                $course,
                $call,
                $kind,
                $questions,
                $entry[6] ?? true
            );
        }

        return $pages;
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function madridFisicaClassicQuestions(): array
    {
        return [
            ['Opción A', 'Pregunta 1', 'Resolver un ejercicio de gravitación con masas, satélites, órbitas, energía o velocidad de escape.', 'Campo gravitatorio'],
            ['Opción A', 'Pregunta 2', 'Trabajar una cuestión de vibraciones, ondas mecánicas, sonido, intensidad sonora o movimiento armónico.', 'Vibraciones, ondas y sonido'],
            ['Opción A', 'Pregunta 3', 'Calcular campos eléctricos o magnéticos, potenciales, fuerzas sobre cargas, corrientes, flujo o inducción.', 'Campo eléctrico, magnético e inducción'],
            ['Opción A', 'Pregunta 4', 'Resolver un apartado de óptica con lentes, espejos, prismas, refracción, reflexión total o sistemas ópticos.', 'Óptica geométrica'],
            ['Opción A', 'Pregunta 5', 'Aplicar física moderna: efecto fotoeléctrico, física cuántica, relatividad, radiactividad o desintegración nuclear.', 'Física cuántica, nuclear y relativista'],
            ['Opción B', 'Pregunta 1', 'Resolver la alternativa de gravitación de la segunda opción del enunciado oficial.', 'Campo gravitatorio'],
            ['Opción B', 'Pregunta 2', 'Resolver la alternativa de vibraciones, ondas o sonido de la segunda opción.', 'Vibraciones, ondas y sonido'],
            ['Opción B', 'Pregunta 3', 'Resolver la alternativa de campo eléctrico, campo magnético o inducción electromagnética.', 'Campo eléctrico, magnético e inducción'],
            ['Opción B', 'Pregunta 4', 'Resolver la alternativa de óptica geométrica de la segunda opción.', 'Óptica geométrica'],
            ['Opción B', 'Pregunta 5', 'Resolver la alternativa de física cuántica, nuclear, relativista o de partículas.', 'Física cuántica, nuclear y relativista'],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function madridFisicaPau2025Questions(): array
    {
        return [
            ['Obligatoria', 'Pregunta 1', 'Resolver la pregunta obligatoria de campo gravitatorio con órbitas, velocidades o energía gravitatoria.', 'Campo gravitatorio'],
            ['A elegir', 'Pregunta 2.A', 'Resolver una alternativa del bloque de campo electromagnético.', 'Campo electromagnético'],
            ['A elegir', 'Pregunta 2.B', 'Resolver la segunda alternativa del bloque de campo electromagnético.', 'Campo electromagnético'],
            ['A elegir', 'Pregunta 3.A', 'Resolver una alternativa del bloque de vibraciones, ondas u óptica.', 'Vibraciones, ondas y óptica'],
            ['A elegir', 'Pregunta 3.B', 'Resolver la segunda alternativa del bloque de vibraciones, ondas u óptica.', 'Vibraciones, ondas y óptica'],
            ['A elegir', 'Pregunta 4.A', 'Resolver una alternativa de física relativista, cuántica, nuclear o de partículas.', 'Física cuántica, nuclear y relativista'],
            ['A elegir', 'Pregunta 4.B', 'Resolver la segunda alternativa de física relativista, cuántica, nuclear o de partículas.', 'Física cuántica, nuclear y relativista'],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function madridFisicaQuestionProblemQuestions(): array
    {
        return [
            ['Opción A', 'Cuestión 1', 'Resolver la primera cuestión teórico-práctica de la opción A con razonamiento, fórmulas y unidades.', 'Gravitación, ondas, óptica, electromagnetismo o física moderna'],
            ['Opción A', 'Cuestión 2', 'Resolver la segunda cuestión teórico-práctica de la opción A.', 'Gravitación, ondas, óptica, electromagnetismo o física moderna'],
            ['Opción A', 'Cuestión 3', 'Resolver la tercera cuestión teórico-práctica de la opción A.', 'Gravitación, ondas, óptica, electromagnetismo o física moderna'],
            ['Opción A', 'Problema 1', 'Desarrollar el primer problema largo de la opción A con varios apartados.', 'Mecánica, gravitación, ondas u óptica'],
            ['Opción A', 'Problema 2', 'Desarrollar el segundo problema largo de la opción A.', 'Electromagnetismo, óptica, física cuántica o nuclear'],
            ['Opción B', 'Cuestión 1', 'Resolver la primera cuestión teórico-práctica de la opción B.', 'Gravitación, ondas, óptica, electromagnetismo o física moderna'],
            ['Opción B', 'Cuestión 2', 'Resolver la segunda cuestión teórico-práctica de la opción B.', 'Gravitación, ondas, óptica, electromagnetismo o física moderna'],
            ['Opción B', 'Cuestión 3', 'Resolver la tercera cuestión teórico-práctica de la opción B.', 'Gravitación, ondas, óptica, electromagnetismo o física moderna'],
            ['Opción B', 'Problema 1', 'Desarrollar el primer problema largo de la opción B con varios apartados.', 'Mecánica, gravitación, ondas u óptica'],
            ['Opción B', 'Problema 2', 'Desarrollar el segundo problema largo de la opción B.', 'Electromagnetismo, óptica, física cuántica o nuclear'],
        ];
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function madridFisicaLogseQuestions(): array
    {
        return [
            ['Primera parte', 'Cuestiones 1-5', 'Responder tres de cinco cuestiones teóricas, conceptuales o teórico-prácticas.', 'Gravitación, ondas, óptica, electromagnetismo y física moderna'],
            ['Repertorio A', 'Problema 1', 'Resolver el primer problema del repertorio A, elegido como bloque completo de la segunda parte.', 'Problemas de cálculo y razonamiento físico'],
            ['Repertorio A', 'Problema 2', 'Resolver el segundo problema del repertorio A, sin mezclarlo con el repertorio B.', 'Problemas de cálculo y razonamiento físico'],
            ['Repertorio B', 'Problema 1', 'Resolver el primer problema del repertorio B como alternativa completa al repertorio A.', 'Problemas de cálculo y razonamiento físico'],
            ['Repertorio B', 'Problema 2', 'Resolver el segundo problema del repertorio B como alternativa completa al repertorio A.', 'Problemas de cálculo y razonamiento físico'],
        ];
    }

    /**
     * @param list<array{0: string, 1: string, 2: string, 3: string}> $questions
     *
     * @return array<string, mixed>
     */
    private function madridFisicaPage(
        string $slug,
        string $name,
        string $course,
        string $call,
        string $kind,
        array $questions,
        bool $hasSolution
    ): array {
        $topicLabels = array_values(array_unique(array_map(static fn (array $question): string => $question[3], $questions)));
        $topicSummary = implode(', ', array_slice($topicLabels, 0, 5));
        $callLower = mb_strtolower($call);
        $titleEnding = $hasSolution ? 'enunciado, temas y solución' : 'enunciado y temas';
        $metaTitleEnding = $hasSolution ? 'Enunciado y solución' : 'Enunciado y temas';

        return [
            'metaTitle' => 'PAU Madrid ' . $name . ' Física | ' . $metaTitleEnding,
            'title' => 'PAU Madrid ' . $name . ' Física: ' . $titleEnding,
            'metaDescription' => $hasSolution
                ? 'Consulta el examen PAU/EvAU Madrid ' . $name . ' de Física: enunciado, datos del examen, bloques, temas, dificultad y acceso a la solución.'
                : 'Consulta el examen PAU/EvAU Madrid ' . $name . ' de Física: enunciado, datos del examen, bloques, temas y dificultad.',
            'summaryTitle' => 'Resumen del examen',
            'summaryParagraphs' => [
                'Este examen PAU/EvAU de Física de Madrid corresponde a ' . $callLower . ' del curso ' . $course . '. Reúne ejercicios de ' . $topicSummary . ', con problemas numéricos y cuestiones de razonamiento físico.',
                'La prueba dura 90 minutos y reparte la puntuación entre los ejercicios indicados en el enunciado oficial. En esta página puedes abrir el enunciado, revisar los temas de cada bloque y acceder al pack PAU de Física Madrid.',
            ],
            'solutionCta' => [
                'eyebrow' => $hasSolution ? 'Solución de ' . $call : 'Pack PAU Física Madrid',
                'title' => $hasSolution ? 'Corrige este examen con la solución completa' : 'Practica este examen con el pack completo',
                'text' => $hasSolution
                    ? 'El pack PAU {subjectName} {communityName} incluye la solución de este examen y el histórico {yearRange} de enunciados y soluciones para practicar sin buscar año por año.'
                    : 'El pack PAU {subjectName} {communityName} reúne el histórico {yearRange} de enunciados y soluciones disponibles para practicar sin buscar año por año.',
                'priceText' => 'Pago único de {formattedPrice}, sin suscripción.',
                'buttonLabel' => $hasSolution ? 'Ver solución y pack completo' : 'Ver pack completo',
                'eventLabel' => 'exam-main-solution-cta-seo-madrid-physics-' . $slug,
            ],
            'statementTitle' => $hasSolution ? 'Enunciado y solución' : 'Enunciado oficial',
            'visibleFileLabel' => 'Ver enunciado oficial de ' . $call,
            'visibleFileTitle' => 'Ver el enunciado de PAU Madrid ' . $name . ' de Física',
            'lockedFileLabel' => 'Ver solución completa en el pack PAU {subjectName} {communityName}',
            'premiumFileLabel' => 'Ver {fileName} con Premium',
            'examDataTitle' => 'Datos del examen',
            'examData' => [
                ['label' => 'Prueba', 'value' => 'PAU/EvAU Madrid'],
                ['label' => 'Asignatura', 'value' => 'Física'],
                ['label' => 'Curso', 'value' => $course],
                ['label' => 'Convocatoria', 'value' => $call],
                ['label' => 'Duración', 'value' => '90 minutos'],
                ['label' => 'Calificación', 'value' => $kind === 'modelo' ? 'Consulta la puntuación y la opcionalidad en el modelo oficial' : 'Consulta la puntuación de cada ejercicio en el enunciado oficial'],
                ['label' => 'Dificultad estimada', 'value' => '{difficulty}/10'],
            ],
            'questionsTitle' => 'Preguntas de PAU Madrid ' . $name . ' de Física',
            'questions' => array_map(static fn (array $question): array => [
                'block' => $question[0],
                'question' => $question[1],
                'task' => $question[2],
                'topic' => $question[3],
            ], $questions),
            'topicsTitle' => 'Temas que aparecen',
            'topics' => array_map(static fn (string $topic): string => $topic . '.', $topicLabels),
            'practiceTitle' => 'Cómo practicar este examen',
            'practiceSteps' => [
                'Haz primero una pregunta de cada bloque respetando el límite de 90 minutos.',
                'Anota las fórmulas usadas, las unidades y los cambios de escala antes de corregir.',
                'Corrige el examen con la solución y separa los fallos de concepto, planteamiento y cálculo.',
                'Refuerza los mismos bloques con otros exámenes de Física de Madrid.',
            ],
            'relatedTitle' => 'Exámenes relacionados',
            'relatedExams' => $this->madridFisicaRelatedExams($slug),
            'quickFactsTitle' => 'Ficha rápida',
            'quickFacts' => [
                'Comunidad: Madrid',
                'Asignatura: Física',
                'Convocatoria: ' . $call,
                'Nivel: 2º Bachillerato',
                'Tiempo: 90 minutos',
            ],
            'educationalLevel' => '2º Bachillerato',
            'learningResourceTypes' => $hasSolution
                ? ['Examen PAU', 'Examen de selectividad', 'Solución de examen']
                : ['Examen PAU', 'Examen de selectividad', 'Enunciado de examen'],
            'schemaAbout' => array_merge([
                'PAU Madrid',
                'EvAU Madrid',
                'Física',
            ], $topicLabels),
            'analyticsLabel' => 'exam-seo-madrid-physics-' . $slug,
            'sidebarPackEventLabel' => 'exam-sidebar-seo-madrid-physics-' . $slug,
            'authorName' => 'Juan Carlos Rojo',
            'authorJobTitle' => 'Profesor de apoyo especializado en PAU, Bachillerato y ESO',
        ];
    }

    /**
     * @return list<array{label: string, type: string, examSlug?: string}>
     */
    private function madridFisicaRelatedExams(string $currentSlug): array
    {
        $relatedExams = [
            ['label' => 'Todos los exámenes de PAU Física Madrid', 'type' => 'subject'],
        ];

        foreach ([
            ['label' => 'PAU Madrid 2025 Física junio', 'type' => 'exam', 'examSlug' => '2025-junio-2'],
            ['label' => 'PAU Madrid 2024 Física junio', 'type' => 'exam', 'examSlug' => '2024-junio-3'],
            ['label' => 'Modelo PAU Madrid 2024 Física', 'type' => 'exam', 'examSlug' => '2024-modelo-3'],
        ] as $candidate) {
            if ($candidate['examSlug'] !== $currentSlug) {
                $relatedExams[] = $candidate;
            }

            if (\count($relatedExams) === 3) {
                break;
            }
        }

        return $relatedExams;
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function madridQuimicaPages(): array
    {
        $entries = [
            ['2022-modelo', '2022 modelo', '2021/2022', 'Modelo 2022', 'modelo'],
            ['2021-julio-extraordinaria', '2021 Julio Extraordinaria', '2020/2021', 'Julio extraordinaria 2021', 'extraordinaria'],
            ['2021-junio', '2021 Junio', '2020/2021', 'Junio 2021', 'ordinaria'],
            ['2021-modelo', '2021 modelo', '2020/2021', 'Modelo 2021', 'modelo'],
            ['2020-modelo', '2020 modelo', '2019/2020', 'Modelo 2020', 'modelo'],
            ['2020-junio', '2020 Junio', '2019/2020', 'Junio 2020', 'ordinaria'],
            ['2020-septiembre', '2020 Septiembre', '2019/2020', 'Septiembre 2020', 'extraordinaria'],
            ['2019-modelo', '2019 modelo', '2018/2019', 'Modelo 2019', 'modelo', false],
            ['2019-junio-ordinaria', '2019 Junio Ordinaria', '2018/2019', 'Junio ordinaria 2019', 'ordinaria'],
            ['2019-julio-extraordinaria', '2019 Julio Extraordinaria', '2018/2019', 'Julio extraordinaria 2019', 'extraordinaria'],
            ['2018-modelo', '2018 modelo', '2017/2018', 'Modelo 2018', 'modelo'],
            ['2018-junio', '2018 Junio', '2017/2018', 'Junio 2018', 'ordinaria'],
            ['2018-julio-extraordinaria', '2018 Julio Extraordinaria', '2017/2018', 'Julio extraordinaria 2018', 'extraordinaria'],
            ['2017-junio', '2017 Junio', '2016/2017', 'Junio 2017', 'ordinaria'],
            ['2017-septiembre', '2017 Septiembre', '2016/2017', 'Septiembre 2017', 'extraordinaria'],
            ['2016-septiembre', '2016 Septiembre', '2015/2016', 'Septiembre 2016', 'extraordinaria'],
            ['2016-junio', '2016 Junio', '2015/2016', 'Junio 2016', 'ordinaria'],
            ['2016-modelo', '2016 modelo', '2015/2016', 'Modelo 2016', 'modelo'],
            ['2015-junio', '2015 Junio', '2014/2015', 'Junio 2015', 'ordinaria'],
            ['2015-modelo', '2015 modelo', '2014/2015', 'Modelo 2015', 'modelo'],
            ['2015-septiembre', '2015 Septiembre', '2014/2015', 'Septiembre 2015', 'extraordinaria'],
        ];

        $pages = [];
        foreach ($entries as $entry) {
            [$slug, $name, $course, $call, $kind] = $entry;

            $pages['selectividad/madrid/quimica/' . $slug] = $this->madridQuimicaPage(
                $slug,
                $name,
                $course,
                $call,
                $kind,
                $entry[5] ?? true
            );
        }

        return $pages;
    }

    /**
     * @return list<array{0: string, 1: string, 2: string, 3: string}>
     */
    private function madridQuimicaQuestions(): array
    {
        return [
            ['Opción A', 'Pregunta A1', 'Resolver una cuestión de estructura atómica, configuración electrónica, tabla periódica, enlace o geometría molecular.', 'Estructura atómica, tabla periódica y enlace'],
            ['Opción A', 'Pregunta A2', 'Trabajar con equilibrio químico, cinética, solubilidad, ácido-base, formulación o química orgánica.', 'Equilibrio, cinética, solubilidad, ácido-base y orgánica'],
            ['Opción A', 'Pregunta A3', 'Resolver un ejercicio de cálculo químico con equilibrio, pH, precipitación, redox, termoquímica o velocidad de reacción.', 'Cálculo químico y razonamiento experimental'],
            ['Opción A', 'Pregunta A4', 'Aplicar formulación, nomenclatura, reacciones orgánicas, electroquímica, pilas o electrólisis.', 'Formulación, orgánica y electroquímica'],
            ['Opción A', 'Pregunta A5', 'Resolver el último bloque de la opción A, normalmente con redox, equilibrio, ácido-base, orgánica o electroquímica.', 'Redox, equilibrio y química aplicada'],
            ['Opción B', 'Pregunta B1', 'Resolver la alternativa de estructura, enlace, propiedades periódicas o geometría molecular de la opción B.', 'Estructura atómica, tabla periódica y enlace'],
            ['Opción B', 'Pregunta B2', 'Resolver la alternativa de cinética, equilibrio, formulación, ácido-base u orgánica de la opción B.', 'Equilibrio, cinética, solubilidad, ácido-base y orgánica'],
            ['Opción B', 'Pregunta B3', 'Resolver la alternativa de cálculo químico, estequiometría, solubilidad, redox o termoquímica.', 'Cálculo químico y razonamiento experimental'],
            ['Opción B', 'Pregunta B4', 'Resolver la alternativa de electroquímica, equilibrio, ácido-base, orgánica o formulación.', 'Formulación, orgánica y electroquímica'],
            ['Opción B', 'Pregunta B5', 'Resolver el último bloque de la opción B, normalmente con redox, equilibrio, ácido-base, orgánica o electroquímica.', 'Redox, equilibrio y química aplicada'],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    private function madridQuimicaPage(
        string $slug,
        string $name,
        string $course,
        string $call,
        string $kind,
        bool $hasSolution
    ): array {
        $questions = $this->madridQuimicaQuestions();
        $topicLabels = array_values(array_unique(array_map(static fn (array $question): string => $question[3], $questions)));
        $topicSummary = implode(', ', array_slice($topicLabels, 0, 5));
        $callLower = mb_strtolower($call);
        $titleEnding = $hasSolution ? 'enunciado, temas y solución' : 'enunciado y temas';
        $metaTitleEnding = $hasSolution ? 'Enunciado y solución' : 'Enunciado y temas';

        return [
            'metaTitle' => 'PAU Madrid ' . $name . ' Química | ' . $metaTitleEnding,
            'title' => 'PAU Madrid ' . $name . ' Química: ' . $titleEnding,
            'metaDescription' => $hasSolution
                ? 'Consulta el examen PAU/EvAU Madrid ' . $name . ' de Química: enunciado, datos del examen, opciones, temas, dificultad y acceso a la solución.'
                : 'Consulta el examen PAU/EvAU Madrid ' . $name . ' de Química: enunciado, datos del examen, opciones, temas y dificultad.',
            'summaryTitle' => 'Resumen del examen',
            'summaryParagraphs' => [
                'Este examen PAU/EvAU de Química de Madrid corresponde a ' . $callLower . ' del curso ' . $course . '. Reúne ejercicios de ' . $topicSummary . ', con cuestiones de razonamiento químico y problemas de cálculo.',
                'La prueba dura 90 minutos y reparte la puntuación entre las preguntas indicadas en el enunciado oficial. En esta página puedes abrir el enunciado, revisar las opciones y acceder al pack PAU de Química Madrid.',
            ],
            'solutionCta' => [
                'eyebrow' => $hasSolution ? 'Solución de ' . $call : 'Pack PAU Química Madrid',
                'title' => $hasSolution ? 'Corrige este examen con la solución completa' : 'Practica este examen con el pack completo',
                'text' => $hasSolution
                    ? 'El pack PAU {subjectName} {communityName} incluye la solución de este examen y el histórico {yearRange} de enunciados y soluciones para practicar sin buscar año por año.'
                    : 'El pack PAU {subjectName} {communityName} reúne el histórico {yearRange} de enunciados y soluciones disponibles para practicar sin buscar año por año.',
                'priceText' => 'Pago único de {formattedPrice}, sin suscripción.',
                'buttonLabel' => $hasSolution ? 'Ver solución y pack completo' : 'Ver pack completo',
                'eventLabel' => 'exam-main-solution-cta-seo-madrid-chemistry-' . $slug,
            ],
            'statementTitle' => $hasSolution ? 'Enunciado y solución' : 'Enunciado oficial',
            'visibleFileLabel' => 'Ver enunciado oficial de ' . $call,
            'visibleFileTitle' => 'Ver el enunciado de PAU Madrid ' . $name . ' de Química',
            'lockedFileLabel' => 'Ver solución completa en el pack PAU {subjectName} {communityName}',
            'premiumFileLabel' => 'Ver {fileName} con Premium',
            'examDataTitle' => 'Datos del examen',
            'examData' => [
                ['label' => 'Prueba', 'value' => 'PAU/EvAU Madrid'],
                ['label' => 'Asignatura', 'value' => 'Química'],
                ['label' => 'Curso', 'value' => $course],
                ['label' => 'Convocatoria', 'value' => $call],
                ['label' => 'Duración', 'value' => '90 minutos'],
                ['label' => 'Calificación', 'value' => $kind === 'modelo' ? 'Consulta la puntuación y la opcionalidad en el modelo oficial' : 'Consulta la puntuación de cada pregunta en el enunciado oficial'],
                ['label' => 'Dificultad estimada', 'value' => '{difficulty}/10'],
            ],
            'questionsTitle' => 'Preguntas de PAU Madrid ' . $name . ' de Química',
            'questions' => array_map(static fn (array $question): array => [
                'block' => $question[0],
                'question' => $question[1],
                'task' => $question[2],
                'topic' => $question[3],
            ], $questions),
            'topicsTitle' => 'Temas que aparecen',
            'topics' => array_map(static fn (string $topic): string => $topic . '.', $topicLabels),
            'practiceTitle' => 'Cómo practicar este examen',
            'practiceSteps' => [
                'Lee primero la opcionalidad del enunciado y decide si vas a practicar una opción completa o preguntas alternas.',
                'Resuelve cada pregunta escribiendo el razonamiento químico, las ecuaciones ajustadas, las unidades y los datos usados.',
                'Corrige con la solución y separa los fallos de formulación, planteamiento, cálculo y justificación.',
                'Refuerza los bloques repetidos con otros exámenes de Química de Madrid.',
            ],
            'relatedTitle' => 'Exámenes relacionados',
            'relatedExams' => $this->madridQuimicaRelatedExams($slug),
            'quickFactsTitle' => 'Ficha rápida',
            'quickFacts' => [
                'Comunidad: Madrid',
                'Asignatura: Química',
                'Convocatoria: ' . $call,
                'Nivel: 2º Bachillerato',
                'Tiempo: 90 minutos',
            ],
            'educationalLevel' => '2º Bachillerato',
            'learningResourceTypes' => $hasSolution
                ? ['Examen PAU', 'Examen de selectividad', 'Solución de examen']
                : ['Examen PAU', 'Examen de selectividad', 'Enunciado de examen'],
            'schemaAbout' => array_merge([
                'PAU Madrid',
                'EvAU Madrid',
                'Química',
            ], $topicLabels),
            'analyticsLabel' => 'exam-seo-madrid-chemistry-' . $slug,
            'sidebarPackEventLabel' => 'exam-sidebar-seo-madrid-chemistry-' . $slug,
            'authorName' => 'Juan Carlos Rojo',
            'authorJobTitle' => 'Profesor de apoyo especializado en PAU, Bachillerato y ESO',
        ];
    }

    /**
     * @return list<array{label: string, type: string, examSlug?: string}>
     */
    private function madridQuimicaRelatedExams(string $currentSlug): array
    {
        $relatedExams = [
            ['label' => 'Todos los exámenes de PAU Química Madrid', 'type' => 'subject'],
        ];

        foreach ([
            ['label' => 'Modelo PAU Madrid 2022 Química', 'type' => 'exam', 'examSlug' => '2022-modelo'],
            ['label' => 'PAU Madrid 2021 Química junio', 'type' => 'exam', 'examSlug' => '2021-junio'],
            ['label' => 'Modelo PAU Madrid 2021 Química', 'type' => 'exam', 'examSlug' => '2021-modelo'],
        ] as $candidate) {
            if ($candidate['examSlug'] !== $currentSlug) {
                $relatedExams[] = $candidate;
            }

            if (\count($relatedExams) === 3) {
                break;
            }
        }

        return $relatedExams;
    }
}
