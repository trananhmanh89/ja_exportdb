<?php 
$demo = $this->profileConfig->get('demo', array());
$demoTemplateStylesSelected = isset($demo->ts) ? $demo->ts : array();
$qs = $this->profileConfig->get('qs');
$qsTemplateStylesSelected = isset($qs->ts) ? $qs->ts : array();
?>
<div class="config-template-styles">
    <table class="table table-sm table-bordered template-styles-table">
        <tbody>
            <?php foreach ($this->project->templateStyles as $item): ?>
                <thead class="thead-light">
                    <tr>
                        <th width="40%"><?php echo $item->name ?></th>
                        <th width="15%">
                            <div class="custom-control custom-checkbox hide-input">
                                <label 
                                    class="custom-control-label" 
                                    for="<?php echo 'ts-qs-' . $item->name ?>">
                                    QS
                                </label>
                            </div>
                        </th>
                        <th width="15%">
                            <div class="custom-control custom-checkbox hide-input">
                                <label 
                                    class="custom-control-label" 
                                    for="<?php echo 'ts-demo-' . $item->name ?>">
                                    Demo
                                </label>
                            </div>
                        </th>
                    </tr>
                </thead>
                <?php foreach ($item->styles as $key => $style): ?>
                    <tr>
                        <td><?php echo $style->title ?></td>
                        <td>
                            <input 
                                class="item-cb" 
                                data-export="qs"
                                data-type="<?php echo $item->name . '-qs-ts' ?>" 
                                data-index="<?php echo $key ?>"
                                type="checkbox" 
                                name="qs[ts][]" 
                                value="<?php echo $style->id ?>"
                                <?php echo in_array($style->id, $qsTemplateStylesSelected) ? 'checked' : '' ?>
                                >
                        </td>
                        <td>
                            <input 
                                class="item-cb" 
                                data-export="demo"
                                data-type="<?php echo $item->name . '-demo-ts' ?>" 
                                data-index="<?php echo $key ?>"
                                type="checkbox" 
                                name="demo[ts][]" 
                                value="<?php echo $style->id ?>"
                                <?php echo in_array($style->id, $demoTemplateStylesSelected) ? 'checked' : '' ?>
                                >
                        </td>
                    </tr>
                <?php endforeach ?>
            <?php endforeach ?>
        </tbody>
    </table>
</div>