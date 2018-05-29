<?php

namespace App\DataFixtures;

use App\Entity\Category;
use App\Entity\Status;
use App\Entity\Trick;
use App\Entity\User;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class Tricks extends Fixture
{
    public function load(ObjectManager $manager)
    {
	    //Category
	    $grab = (new Category())
		    ->setName('grab');
	    $manager->persist($grab);

	    $rotations = (new Category())
		    ->setName('rotations');
	    $manager->persist($rotations);

	    $flips = (new Category())
		    ->setName('flips');
	    $manager->persist($flips);

	    //Status & User
	    $status = (new Status())
		    ->setName('Utilisateur');
	    $manager->persist($status);

	    $user1 = (new User())
		    ->setFirstName('Léo')
		    ->setLastName('Loubatière')
		    ->setPseudo('Unicorn')
		    ->setStatus($status)
		    ->setPassword(hash(
		    	'sha512',
			    strlen('jOn79613226') . 'jOn79613226'
		    ))
		    ->setMail('llouba@gmail.com')
		    ->setMailValidate(true)
		    ->setPhone('0606060606');
	    $manager->persist($user1);

	    $status = (new Status())
		    ->setName('Contributeur');
	    $manager->persist($status);

	    $user2 = (new User())
		    ->setFirstName('Régis')
		    ->setLastName('Puthod')
		    ->setPseudo('KoldoRex')
		    ->setStatus($status)
		    ->setPassword(hash(
			    'sha512',
			    strlen('jOn79613226') . 'jOn79613226'
		    ))
		    ->setMail('rex@gmail.com')
		    ->setMailValidate(true)
		    ->setPhone('0650405030');
	    $manager->persist($user2);

	    $status = (new Status())
		    ->setName('Administrateur');
	    $manager->persist($status);

	    $user3 = (new User())
		    ->setFirstName('Jonathan')
		    ->setLastName('Marco')
		    ->setPseudo('Giildo')
		    ->setStatus($status)
		    ->setPassword(hash(
			    'sha512',
			    strlen('jOn79613226') . 'jOn79613226'
		    ))
		    ->setMail('giildo.jm@gmail.com')
		    ->setMailValidate(true)
		    ->setPhone('0630794953');
	    $manager->persist($user3);

	    //Trick
	    $trick = (new Trick())
		    ->setName('Mute')
		    ->setUser($user3)
		    ->setUpdatedAt(new DateTime())
		    ->setCreatedAt(new DateTime())
		    ->setCategory($grab)
		    ->setDescription('Saisie de la carre frontside de la planche entre les deux pieds avec la main avant')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('180°')
		    ->setUser($user1)
		    ->setUpdatedAt(new DateTime('2015-05-15 15:15:21'))
		    ->setCreatedAt(new DateTime('2015-05-15 15:15:21'))
		    ->setCategory($rotations)
		    ->setDescription('Désigne un demi-tour du surfer.')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('Indy')
		    ->setUser($user1)
		    ->setUpdatedAt(new DateTime('2017-02-24 14:10:58'))
		    ->setCreatedAt(new DateTime('2018-01-01 10:10:25'))
		    ->setCategory($grab)
		    ->setDescription('Saisie de la carre frontside de la planche, entre les deux pieds, avec la main arrière.')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('Stalefish')
		    ->setUser($user2)
		    ->setUpdatedAt(new DateTime())
		    ->setCreatedAt(new DateTime())
		    ->setCategory($grab)
		    ->setDescription('Saisie de la carre backside de la planche entre les deux pieds avec la main arrière.')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('Front flips')
		    ->setUser($user1)
		    ->setUpdatedAt(new DateTime('2002-01-05 14:15:26'))
		    ->setCreatedAt(new DateTime('2002-01-05 14:15:26'))
		    ->setCategory($flips)
		    ->setDescription('Rotation verticale vers l\'avant.')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('Back flips')
		    ->setUser($user1)
		    ->setUpdatedAt(new DateTime('2002-01-05 14:15:26'))
		    ->setCreatedAt(new DateTime('2002-01-05 14:15:26'))
		    ->setCategory($flips)
		    ->setDescription('Rotation verticale vers l\'arrière.')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('360°')
		    ->setUser($user3)
		    ->setUpdatedAt(new DateTime())
		    ->setCreatedAt(new DateTime())
		    ->setCategory($rotations)
		    ->setDescription('Lorsque le snowboarder fait un tour complet. Appelé aussi un "trois six".')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('540°')
		    ->setUser($user3)
		    ->setUpdatedAt(new DateTime())
		    ->setCreatedAt(new DateTime())
		    ->setCategory($rotations)
		    ->setDescription('Lorsque le snowboarder fait un tour complet. Appelé aussi un "cinq quatre".')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('Nose grab')
		    ->setUser($user2)
		    ->setUpdatedAt(new DateTime('2018-01-07'))
		    ->setCreatedAt(new DateTime('2018-01-07'))
		    ->setCategory($rotations)
		    ->setDescription('Saisie de la partie avant de la planche, avec la main avant.')
		    ->setPublished(true);
	    $manager->persist($trick);

	    $trick = (new Trick())
		    ->setName('Tail grab')
		    ->setUser($user3)
		    ->setUpdatedAt(new DateTime('2016-09-09 09:09:09'))
		    ->setCreatedAt(new DateTime('2016-09-09 09:09:09'))
		    ->setCategory($rotations)
		    ->setDescription('Saisie de la partie arrière de la planche, avec la main arrière.')
		    ->setPublished(true);
	    $manager->persist($trick);

        $manager->flush();
    }
}
