<?php

/*
 * This file is part of the Doctrine Behavioral Extensions package.
 * (c) Gediminas Morkevicius <gediminas.morkevicius@gmail.com> http://www.gediminasm.org
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Filter;

use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Query\Filter\SQLFilter;
use Gedmo\SoftDeleteable\SoftDeleteableListener;

/**
 * The SoftDeleteableFilter adds the condition necessary to
 * filter entities which were deleted "softly"
 *
 * @author Gustavo Falco <comfortablynumb84@gmail.com>
 * @author Gediminas Morkevicius <gediminas.morkevicius@gmail.com>
 * @author Patrik Votoček <patrik@votocek.cz>
 *
 * @final since gedmo/doctrine-extensions 3.11
 */
class CurrentUserQueryFilter extends SQLFilter {
    private $security;

    public function setSecurity($security)
    {
        $this->security = $security;
    }

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->getTableName() != "location" && 
            $targetEntity->hasAssociation("user")) {
            if (!$this->security) {
                return '';
            }

            $user = $this->security->getUser();
            if (!$user) {
                return '';
            }

            $userId = $user->getId();
            if (!$userId) {
                return '';
            }

            return $targetTableAlias . ".user_id = '" . $userId . "'";
        }
        return "";
    }

}
