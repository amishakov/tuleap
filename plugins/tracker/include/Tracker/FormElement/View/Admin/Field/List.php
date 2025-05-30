<?php
/**
 * Copyright (c) Enalean, 2012 - Present. All Rights Reserved.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */


class Tracker_FormElement_View_Admin_Field_List extends Tracker_FormElement_View_Admin_Field  // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace,Squiz.Classes.ValidClassName.NotCamelCaps
{
    /**
     * Fetch additionnal stuff to display below the edit form
     *
     * @return string html
     */
    public function fetchAfterAdminEditForm()
    {
        return $this->formElement->getBind()->fetchAdminEditForm();
    }

    /**
     * Fetch additionnal stuff to display below the create form
     * Result if not empty must be enclosed in a <tr>
     *
     * @return string html
     */
    public function fetchAfterAdminCreateForm()
    {
        $bf    = new Tracker_FormElement_Field_List_BindFactory(new \Tuleap\DB\DatabaseUUIDV7Factory());
        $html  = '';
        $html .= '<tr valign="top"><td colspan="2">';
        $html .= $bf->fetchCreateABind($this->formElement);
        $html .= '</td></tr>';
        return $html;
    }
}
