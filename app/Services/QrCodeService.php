<?php

namespace App\Services;

use App\Models\Student;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;

/**
 * Builds the permanent identity QR code that every student carries. The code
 * never changes between attendance sessions: it only encodes who the student
 * is (NISN, name, class) plus a signature so the payload cannot be forged.
 */
class QrCodeService
{
    /**
     * Render arbitrary text as an SVG QR code.
     */
    public function svg(string $content, int $size = 240): string
    {
        $renderer = new ImageRenderer(
            new RendererStyle($size, 1),
            new SvgImageBackEnd,
        );

        $svg = (new Writer($renderer))->writeString($content);

        // Drop the XML prolog so the markup can be embedded straight into HTML.
        return (string) preg_replace('/<\?xml.*?\?>\s*/s', '', $svg);
    }

    /**
     * The permanent, signed payload for a student.
     *
     * @return array<string, int|string>
     */
    public function payload(Student $student): array
    {
        $student->loadMissing(['user', 'schoolClass']);

        $data = [
            'v' => 1,
            'student_id' => $student->id,
            'nisn' => (string) $student->nisn,
            'nis' => (string) $student->nis,
            'name' => $student->user?->name ?? 'Siswa',
            'class' => $student->schoolClass?->name ?? '-',
            'class_id' => (int) $student->class_id,
        ];

        $data['sig'] = $this->signature($data);

        return $data;
    }

    /**
     * The signed payload encoded as the JSON string stored inside the QR code.
     */
    public function encode(Student $student): string
    {
        return (string) json_encode($this->payload($student), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }

    /**
     * Convenience helper: the ready-to-embed SVG for a student.
     */
    public function svgFor(Student $student, int $size = 240): string
    {
        return $this->svg($this->encode($student), $size);
    }

    /**
     * Decode a scanned payload and confirm its signature.
     *
     * @return array<string, int|string>|null
     */
    public function decode(?string $payload): ?array
    {
        if ($payload === null || $payload === '') {
            return null;
        }

        $data = json_decode($payload, true);

        if (! is_array($data) || ! isset($data['sig']) || ! is_string($data['sig'])) {
            return null;
        }

        if (! hash_equals($this->signature($data), $data['sig'])) {
            return null;
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function signature(array $data): string
    {
        $content = implode('|', [
            $data['v'] ?? '',
            $data['student_id'] ?? '',
            $data['nisn'] ?? '',
            $data['nis'] ?? '',
            $data['name'] ?? '',
            $data['class_id'] ?? '',
        ]);

        return hash_hmac('sha256', $content, (string) config('app.key'));
    }
}
