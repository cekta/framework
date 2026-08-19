<?= '<?php' . PHP_EOL; ?><?php
/**
 * @var string $class
 * @var string $namespace
 * @var string $id
 */
?>

declare(strict_types=1);

<?php if (!empty($namespace)) { ?>
namespace <?= $namespace ?>;

<?php } ?>
use Cekta\Migrator\Migration;

class <?= $class ?> implements Migration
{
    public function __construct()
    {
        // TODO: add your dependency like a PDO
    }

    public function up(): void
    {
        // TODO: Implement up() method.
    }

    public function down(): void
    {
        // TODO: Implement down() method.
    }

    public function priority(): int
    {
        return <?= $id ?>;
    }
}