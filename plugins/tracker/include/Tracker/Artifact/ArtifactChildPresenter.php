<?php
/**
 * Copyright (c) Enalean, 2013-Present. All rights reserved
 *
 * This file is a part of Tuleap.
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
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/
 */

use Tuleap\Tracker\Artifact\Artifact;
use Tuleap\Tracker\FormElement\Field\ArtifactLink\Type\TypeIsChildLinkRetriever;
use Tuleap\Tracker\Semantic\Status\TrackerSemanticStatus;

/**
 * Presenter of the child of an artifact
 */
class Tracker_ArtifactChildPresenter // phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace, Squiz.Classes.ValidClassName.NotCamelCaps
{
    /** @var string */
    public $xref;

    /** @var string */
    public $title;

    /** @var int */
    public $id;

    /** @var string */
    public $url;

    /** @var string */
    public $status;

    /** @var int */
    public $parent_id;

    /** @var bool */
    public $has_children;

    /**
     * @param Artifact                $artifact The child
     * @param Artifact                $parent   The parent
     * @param TrackerSemanticStatus $semantic The status semantic used by the corresponding tracker
     */
    public function __construct(
        Artifact $artifact,
        Artifact $parent,
        TrackerSemanticStatus $semantic,
        TypeIsChildLinkRetriever $retriever,
    ) {
        $base_url = \Tuleap\ServerHostname::HTTPSUrl();

        $this->xref         = $artifact->getXRef();
        $this->title        = $artifact->getTitle() ?? '';
        $this->id           = $artifact->getId();
        $this->url          = $base_url . $artifact->getUri();
        $this->status       = $semantic->getStatus($artifact);
        $this->parent_id    = $parent->getId();
        $this->has_children = $this->hasChildren($artifact, $retriever);
    }

    private function hasChildren(Artifact $artifact, TypeIsChildLinkRetriever $retriever): bool
    {
        if ($artifact->getTracker()->isProjectAllowedToUseType()) {
            $artifact_links = $retriever->getChildren($artifact);
            return count($artifact_links) > 0;
        } else {
            return $artifact->hasChildren();
        }
    }
}
