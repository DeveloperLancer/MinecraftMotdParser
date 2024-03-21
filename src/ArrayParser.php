<?php declare(strict_types=1);
/**
 * @author Jakub Gniecki <kubuspl@onet.eu>
 * @copyright
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace DevLancer\MinecraftMotdParser;

class ArrayParser
{
    private array $list = [];
    private ColorCollection  $colorCollection;
    public function __construct(ColorCollection  $colorCollection)
    {
        $this->colorCollection = $colorCollection;
    }

    public function parse(array $list): array
    {
        $this->list = [];
        $this->generate([$list], new MotdItem());

        return $this->list;
    }

    private function generate(array $list, MotdItemInterface $parent): void
    {
        foreach ($list as $data) {
            $container = clone $parent;

            if (is_string($data)) {
                $container->setText($data);
                $this->list[] = $container;
                continue;
            }

            if (isset($data['color'])) {
                $color = $this->colorCollection->get($data['color']);
                if (!$color)
                    $color = $data['color'];
                $container->setColor($color);
            }

            if (isset($data['bold']))
                $container->setBold((bool) $data['bold']);

            if (isset($data['underlined']))
                $container->setUnderlined((bool) $data['underlined']);

            if (isset($data['strikethrough']))
                $container->setStrikethrough((bool) $data['strikethrough']);

            if (isset($data['italic']))
                $container->setItalic((bool) $data['italic']);

            if (isset($data['text'])) {
                $text = $data['text'];
                $newLine = strpos($text, "\n");
                $container->setText(($newLine === false)? $text : substr($text, 0, $newLine));
            }

            $this->list[] = $container;
            if (isset($newLine) && $newLine !== false && isset($text)) {
                $container = new MotdItem();
                $container->setText("\n");
                $this->list[] = $container;

                if (strlen(substr($text, $newLine+1)) > 0)
                    $this->generate([['text' => substr($text, $newLine+1)]], new MotdItem());

                $container = new MotdItem();
            }

            if (isset($data['extra']))
                $this->generate($data['extra'], clone $container);
        }
    }
    /**
     * @return array
     */
    public function getList(): array
    {
        return $this->list;
    }
}