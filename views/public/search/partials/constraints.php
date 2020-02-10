<?php /** @var $constraint_list Elasticsearch_Model_SearchConstraintList */ ?>
<?php $base_url = strtok($_SERVER['REQUEST_URI'], '?'); ?>
<?php $constraints_to_display = array_filter($constraint_list->getConstraints(), 'isASearchConstraint'); ?>

    <div class="col-md-12 you-searched-box">
        <div>You searched for:</div>

        <?php foreach ($constraints_to_display as $constraint): ?>

            <?php foreach ($constraint->getValues() as $value): ?>
                <?php $value->hide(); ?>
                <div class="you-searched-box__constraint">
                    <span class="you-searched-box__term"><?= $constraint->getKey() ?> = <?= $value->getValue() ?></span>
                    <span class="you-searched-box__delete">
                                <a href="<?= $base_url ?>?<?= $constraint_list ?>">X</a>
                        </span>
                </div>
                <?php $value->show(); ?>
            <?php endforeach; ?>

        <?php endforeach; ?>
        <div class="you-searched-box__new-search"><a href="/items/search">New Search</a></div>
    </div>

<?php

function isASearchConstraint(Elasticsearch_Model_SearchConstraint $constraint)
{
    return $constraint->isSearchParam();
}