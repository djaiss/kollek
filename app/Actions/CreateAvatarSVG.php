<?php

declare(strict_types=1);

namespace App\Actions;

class CreateAvatarSVG
{
    private readonly string $initials;

    private readonly string $background;

    private readonly string $foreground;

    public function __construct(
        string $initials,
        private readonly int $size = 64,
    ) {
        $this->initials = htmlspecialchars(strtoupper(substr($initials, 0, 2)), ENT_XML1);
        $this->background = $this->deriveBackground($this->initials);
        $this->foreground = $this->deriveForeground($this->background);
    }

    public function render(): string
    {
        $half = $this->size / 2;
        $fontSize = (int) ($this->size * 0.4);

        return <<<SVG
    <svg xmlns="http://www.w3.org/2000/svg" width="{$this->size}" height="{$this->size}" viewBox="0 0 {$this->size} {$this->size}" role="img" aria-label="{$this->initials}" style="display:block;">
        <circle cx="{$half}" cy="{$half}" r="{$half}" fill="{$this->background}" />
        <text
            x="{$half}"
            y="{$half}"
            text-anchor="middle"
            dominant-baseline="central"
            font-family="ui-sans-serif, system-ui, sans-serif"
            font-size="{$fontSize}"
            font-weight="600"
            fill="{$this->foreground}"
        >{$this->initials}</text>
    </svg>
    SVG;
    }

    public function toDataUri(): string
    {
        return 'data:image/svg+xml;base64,'.base64_encode($this->render());
    }

    private function deriveBackground(string $initials): string
    {
        $hue = abs(crc32($initials)) % 360;

        return $this->hslToHex($hue, 55, 48);
    }

    private function deriveForeground(string $background): string
    {
        [$r, $g, $b] = sscanf($background, '#%02x%02x%02x');

        // sRGB luminance coefficients (WCAG formula)
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;

        return $luminance > 0.5 ? '#1a1a1a' : '#ffffff';
    }

    private function hslToHex(int $h, int $s, int $l): string
    {
        $s /= 100;
        $l /= 100;

        $c = (1 - abs(2 * $l - 1)) * $s;
        $x = $c * (1 - abs(fmod($h / 60, 2) - 1));
        $m = $l - $c / 2;

        [$r, $g, $b] = match (true) {
            $h < 60 => [$c, $x, 0],
            $h < 120 => [$x, $c, 0],
            $h < 180 => [0, $c, $x],
            $h < 240 => [0, $x, $c],
            $h < 300 => [$x, 0, $c],
            default => [$c, 0, $x],
        };

        return sprintf(
            '#%02x%02x%02x',
            (int) (($r + $m) * 255),
            (int) (($g + $m) * 255),
            (int) (($b + $m) * 255),
        );
    }
}
