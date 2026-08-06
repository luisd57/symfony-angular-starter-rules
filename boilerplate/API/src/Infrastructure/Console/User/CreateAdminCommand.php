<?php

declare(strict_types=1);

namespace App\Infrastructure\Console\User;

use App\Application\User\DTO\Input\CreateAdminInputDTO;
use App\Application\User\Handler\CreateAdminHandler;
use App\Domain\User\Exception\UserAlreadyExistsException;
use App\Infrastructure\Http\Validation\PasswordValidator;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-{{admin_role}}',
    description: 'Create a privileged account',
)]
final class CreateAdminCommand extends Command
{
    public function __construct(
        private readonly CreateAdminHandler $handler,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::REQUIRED, 'Email')
            ->addArgument('name', InputArgument::REQUIRED, 'Full name')
            ->addArgument('password', InputArgument::REQUIRED, 'Password');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        $password = (string) $input->getArgument('password');
        $passwordError = PasswordValidator::validate($password);

        if ($passwordError !== null) {
            $io->error($passwordError);

            return Command::FAILURE;
        }

        try {
            $user = $this->handler->__invoke(new CreateAdminInputDTO(
                email: (string) $input->getArgument('email'),
                fullName: (string) $input->getArgument('name'),
                password: $password,
            ));
        } catch (UserAlreadyExistsException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        } catch (\InvalidArgumentException $exception) {
            $io->error($exception->getMessage());

            return Command::FAILURE;
        }

        $io->success(sprintf('Account created. ID: %s', $user->id));

        return Command::SUCCESS;
    }
}
