<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CodigoRecuperacionMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $codigo;
    public string $correoDestino;

    /**
     * @param string $codigo        El código en texto plano (antes de hashear)
     * @param string $correoDestino Correo del destinatario
     */
    public function __construct(string $codigo, string $correoDestino)
    {
        $this->codigo        = $codigo;
        $this->correoDestino = $correoDestino;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '🔐 Código de Verificación - Génesis Profesional',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.codigo-recuperacion',
        );
    }
}
