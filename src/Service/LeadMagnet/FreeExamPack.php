<?php

namespace App\Service\LeadMagnet;

/**
 * The free PDF pack given in exchange for an email: the exam statements (no
 * solutions) of the last three PAU years in Madrid that the website has, for the
 * four subjects that have a paid pack. The years differ by subject because some
 * years are missing on the website; each PDF lists the years it holds.
 *
 * Statements are already free on the website, so the pack gives away nothing
 * that is sold; the solutions stay in the paid packs.
 *
 * The PDFs live in S3 under lead-magnets/. See docs/runbooks/free-exam-pack.md.
 */
final class FreeExamPack
{
    private const S3_PREFIX = 'lead-magnets/pau-madrid-enunciados-recientes/';

    /**
     * @return list<array{key: string, label: string, path: string, filename: string, packCode: string}>
     */
    public function getFiles(): array
    {
        return [
            $this->file('matematicas-ii', 'Matemáticas II', '2024, 2025 y 2026', 'PAU-Madrid-Matematicas-II-enunciados-recientes.pdf', 'pau_matematicas_ii_madrid_1994_2025'),
            $this->file('matematicas-ccss', 'Matemáticas CCSS', '2021, 2025 y 2026', 'PAU-Madrid-Matematicas-CCSS-enunciados-recientes.pdf', 'pau_matematicas_ccss_madrid_1995_2025'),
            $this->file('fisica', 'Física', '2023, 2024 y 2025', 'PAU-Madrid-Fisica-enunciados-recientes.pdf', 'pau_fisica_madrid_1996_2025'),
            $this->file('quimica', 'Química', '2021, 2022 y 2025', 'PAU-Madrid-Quimica-enunciados-recientes.pdf', 'pau_quimica_madrid_1996_2025'),
        ];
    }

    /**
     * @return array{key: string, label: string, path: string, filename: string, packCode: string}|null
     */
    public function findFile(string $key): ?array
    {
        foreach ($this->getFiles() as $file) {
            if ($file['key'] === $key) {
                return $file;
            }
        }

        return null;
    }

    /**
     * @return array{key: string, label: string, path: string, filename: string, packCode: string}
     */
    private function file(string $key, string $subjectName, string $years, string $filename, string $packCode): array
    {
        return [
            'key' => $key,
            'label' => \sprintf('%s: enunciados de %s', $subjectName, $years),
            'path' => self::S3_PREFIX . $filename,
            'filename' => $filename,
            'packCode' => $packCode,
        ];
    }
}
