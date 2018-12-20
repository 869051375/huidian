<?php
/**
 * This is the template for generating the model class of a specified table.
 */

/* @var $this yii\web\View */
/* @var $generator yii\gii\generators\model\Generator */
/* @var $tableName string full table name */
/* @var $className string class name */
/* @var $queryClassName string query class name */
/* @var $tableSchema yii\db\TableSchema */
/* @var $labels string[] list of attribute labels (name => label) */
/* @var $rules string[] list of validation rules */
/* @var $relations array list of relations (name => relation declaration) */

echo "<?php\n";
?>

namespace <?= $generator->ns ?>;

use Yii;
use yii\base\Model;


/**
 * @SWG\Definition(required={}, @SWG\Xml(name="<?= $className ?>"))
 */
class <?= $className ?> extends Model
{
<?php foreach ($tableSchema->columns as $column): ?>

    /**
     * <?= "{$column->comment}\n" ?>
     * @SWG\Property()
     * @var <?= "{$column->phpType}\n" ?>
     */
    public $<?= "{$column->name};\n" ?>
<?php endforeach; ?>
}
