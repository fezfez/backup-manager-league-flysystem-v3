<?php

declare(strict_types=1);

namespace Fezfez\BackupManager\Tests;

use Exception;
use Fezfez\BackupManager\Filesystems\BackupManagerRessource;
use Fezfez\BackupManager\Filesystems\CantDeleteFile;
use Fezfez\BackupManager\Filesystems\CantReadFile;
use Fezfez\BackupManager\Filesystems\CantWriteFile;
use Fezfez\BackupManager\LeagueFilesystemAdapterV3;
use League\Flysystem\FilesystemOperator;
use PHPUnit\Framework\TestCase;

use function fopen;
use function fwrite;
use function rewind;
use function stream_get_contents;

final class LeagueFilesystemAdapaterV3Test extends TestCase
{
    public function testReadStreamOk(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'my content');
        rewind($stream);

        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('readStream')->with('toto')->willReturn($stream);
        $sUT = new LeagueFilesystemAdapterV3($filesystem);

        self::assertSame('my content', stream_get_contents($sUT->readStream('toto')->getResource()));
    }

    public function testReadStreamFail(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('readStream')->with('toto')->willThrowException(new Exception());
        $sUT = new LeagueFilesystemAdapterV3($filesystem);

        self::expectExceptionCode(0);
        self::expectExceptionMessage('cant read file toto');
        self::expectException(CantReadFile::class);

        $sUT->readStream('toto');
    }

    public function testWriteStreamOk(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'my content');
        rewind($stream);

        $backupManagerRessource = new BackupManagerRessource($stream);

        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('writeStream')->with('toto', $stream);
        $sUT = new LeagueFilesystemAdapterV3($filesystem);

        $sUT->writeStream('toto', $backupManagerRessource);
    }

    public function testWriteStreamFailOnException(): void
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, 'my content');
        rewind($stream);

        $backupManagerRessource = new BackupManagerRessource($stream);

        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('writeStream')->with('toto', $stream)->willThrowException(new Exception());
        $sUT = new LeagueFilesystemAdapterV3($filesystem);

        self::expectExceptionCode(0);
        self::expectExceptionMessage('cant write file toto');
        self::expectException(CantWriteFile::class);

        $sUT->writeStream('toto', $backupManagerRessource);
    }

    public function testDeleteOk(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('delete')->with('toto');
        $sUT = new LeagueFilesystemAdapterV3($filesystem);

        $sUT->delete('toto');
    }

    public function testDeleteFailOnException(): void
    {
        $filesystem = $this->createMock(FilesystemOperator::class);
        $filesystem->expects(self::once())->method('delete')->with('toto')->willThrowException(new Exception('tutu'));
        $sUT = new LeagueFilesystemAdapterV3($filesystem);

        self::expectExceptionCode(0);
        self::expectExceptionMessage('cant delete file toto');
        self::expectException(CantDeleteFile::class);

        $sUT->delete('toto');
    }
}
