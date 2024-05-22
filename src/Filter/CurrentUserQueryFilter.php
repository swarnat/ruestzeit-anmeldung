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

    public function addFilterConstraint(ClassMetadata $targetEntity, $targetTableAlias): string
    {
        if ($targetEntity->hasAssociation("user")) {

            // here how to get the connected user ???
            $userID = 1;
            return $targetTableAlias . ".user_id = '" . $userID . "'";


        }
        return "";
    }    

}