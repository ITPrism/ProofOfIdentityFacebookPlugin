<?php
/**
 * @package      CrowdfundingPartners
 * @subpackage   Plugins
 * @author       Todor Iliev
 * @copyright    Copyright (C) 2015 Todor Iliev <todor@itprism.com>. All rights reserved.
 * @license      GNU General Public License version 3 or later; see LICENSE.txt
 */

defined('_JEXEC') or die; ?>
<div class="panel panel-default">
    <div class="panel-heading">
        <h4><img src="../../../media/com_identityproof/images/facebook-icon-24x24.png">&nbsp;<?php echo JText::_('PLG_IDENTITYPROOF_FACEBOOK_TITLE');?></h4>
    </div>

    <div class="panel-body">
        <?php if (!$profile->getId()) {?>
        <a class="btn btn-primary" href="<?php echo $loginUrl; ?>">
            <span class="fa fa-external-link-square"></span>
            <?php echo JText::_('PLG_IDENTITYPROOF_FACEBOOK_CONNECT');?>
        </a>
        <?php } else { ?>

        <table class="table table-bordered mtb-25-0">
            <thead>
            <tr>
                <th class="col-md-8"><?php echo JText::_('PLG_IDENTITYPROOF_FACEBOOK_ACCOUNT');?></th>
                <th class="col-md-2"><?php echo JText::_('PLG_IDENTITYPROOF_FACEBOOK_STATUS');?></th>
                <th class="col-md-2">&nbsp;</th>
            </tr>
            </thead>
            <tbody>
            <tr>
                <td>
                    <a class="img-rounded" href="<?php echo $profile->getLink(); ?>" target="_blank"><img src="<?php echo $profile->getPicture(); ?>" /></a>
                    <a class="btn btn-link" href="<?php echo $profile->getLink(); ?>" target="_blank">
                        <?php echo htmlspecialchars($profile->getName()); ?>
                    </a>
                </td>
                <td>
                    <?php echo JHtml::_('identityproof.status', $profile->isVerified()); ?>
                </td>
                <td>
                    <a class="btn btn-danger" href="<?php echo JRoute::_('index.php?option=com_identityproof&task=service.remove&service=facebook&'.JSession::getFormToken().'=1');?>">
                        <span class="fa fa-trash"></span>
                        <span class="hidden-xs"><?php echo JText::_('PLG_IDENTITYPROOF_FACEBOOK_REMOVE');?></span>
                    </a>
                </td>
            </tr>
            </tbody>
        </table>
            <?php } ?>


    </div>

</div>

