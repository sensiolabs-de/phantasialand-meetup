<?php declare(strict_types = 1);

namespace App\Command;

use App\Entity\GroupRequest;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Workflow;

class GroupRequestCommand extends Command
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    private $workflow;
    private $doctrine;

    public function __construct(Workflow $workflow, RegistryInterface $doctrine)
    {
        parent::__construct();

        $this->workflow = $workflow;
        $this->doctrine = $doctrine;
    }

    protected function configure()
    {
        $this
            ->setName('app:group-request:process')
            ->setDescription('Command to administer group requests');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Group Request Administration');

        $groups = $this->fetchGroups();

        if (0 === count($groups)) {
            $this->io->success('No groups to process.');

            return 0;
        }

        $this->io->comment(sprintf('There are %d groups to process.', count($groups)));

        $processed = 0;
        while (0 < count($groups)) {
            $group = $this->nextGroup($groups);
            $this->displayGroup($group);

            if ($this->processGroup($group)) {
                ++$processed;
                $this->io->success(
                    sprintf('Group "%s" successful processed: %s.', $group->getUrlname(), $group->getStatus())
                );
            }

            unset($groups[$group->getUrlname()]);

            if (0 < count($groups) && !$this->io->confirm('Do you want to continue?', false)) {
                break;
            }
        }

        $this->io->success(sprintf('Processed %d groups.', $processed));

        return 0;
    }

    /**
     * @return GroupRequest[]
     */
    private function fetchGroups(): array
    {
        $requests = $this->loadConfirmedGroupRequests();

        foreach ($requests as $key => $group) {
            $requests[$group->getUrlname()] = $group;
            unset($requests[$key]);
        }

        return $requests;
    }

    /**
     * @return GroupRequest[]
     */
    private function loadConfirmedGroupRequests(): array
    {
        $repo = $this->doctrine->getRepository(GroupRequest::class);

        return $repo->findBy(
            [
                'status' => GroupRequest::STATUS_CONFIRMED,
            ],
            [
                'createdAt' => 'ASC',
            ]);
    }

    private function nextGroup(array $requests): GroupRequest
    {
        if (1 === count($requests)) {
            return reset($requests);
        }

        $urlname = $this->io->choice('Which group do you want to edit?', array_keys($requests));

        return $requests[$urlname];
    }

    private function displayGroup(GroupRequest $request): void
    {
        $this->io->section(sprintf('Details of group request for "<comment>%s</comment>":', $request->getUrlname()));

        $this->io->table(['Attribute', 'Value'], [
            ['URL-Name', $request->getUrlname()],
            ['E-Mail-Address', $request->getEmail()],
            ['Description', $request->getComment()],
            ['Created at', $request->getCreatedAt()->format('m/d/Y H:i:s')],
            ['Meetup-Link', sprintf('https://www.meetup.com/%s', $request->getUrlname())]
        ]);
    }

    private function processGroup(GroupRequest $request): bool
    {
        $transition = $this->io->choice(
            sprintf('What to do with "<comment>%s</comment>"?', $request->getUrlname()),
            ['skip', 'approve', 'reject']
        );

        if ('skip' === $transition) {
            return false;
        }

        $this->workflow->apply($request, $transition);
        $this->doctrine->getManager()->flush();

        return true;
    }
}
