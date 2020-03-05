<?php


namespace Cactus\User;


use Cactus\I18n\I18nManager;
use Cactus\Util\ClientRequest;
use Mike42\Escpos\EscposImage;
use Mike42\Escpos\Printer;

class UserTicket
{
    private User $user;

    /**
     * UserTicket constructor.
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }


    public function printTicket(Printer $printer)
    {
        $printer->setJustification(Printer::JUSTIFY_CENTER);

        $this->appendImage($printer, "school");

        $printer->setTextSize(3, 3);
        $this->append($printer, "ticket.school_type", 2);
        $printer->setTextSize(2, 2);
        $this->append($printer, "ticket.school_name", 4);

        $firstName = $this->user->getFirstName();
        $lastName = $this->user->getLastName();
        $uniqueId = $this->user->getUniqueId();

        $printer->setTextSize(1, 1);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $this->append($printer, "ticket.hello", 2, [
            $firstName,
            $lastName
        ]);

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $this->append($printer, "ticket.welcome", 1);
        $this->append($printer, "ticket.school_name", 2);
        $this->append($printer, "ticket.where_are_you", 2);


        $printer->setTextSize(2, 2);
        $this->append($printer, "ticket.school_section", 3);

        $printer->setTextSize(1, 1);
        $this->append($printer, "ticket.school_section_option_1", 1);
        $this->append($printer, "ticket.school_section_option_2", 2);

        $barCodeContent = "USER-" . $uniqueId;
        $printer->setTextSize(1, 1);
        $printer->barcode($barCodeContent, Printer::BARCODE_CODE39);

        $printer->feed(2);
        $printer->setTextSize(2, 2);
        $this->append($printer, "ticket.have_fun", 0);
        $this->appendImage($printer, "ok_hand");
    }

    function append(Printer $printer, string $key, int $feed = 2, array $params = [])
    {
        $i18nManager = I18nManager::Instance();
        $clientRequest = ClientRequest::Instance();
        $lang = $clientRequest->getLang();

        $text = $i18nManager->translate($lang, $key);
        $text = vsprintf($text, $params);
        $printer->text($text);

        if ($feed > 0)
            $printer->feed($feed);
    }

    function appendImage(Printer $printer, string $name, int $feed = 1)
    {
        try {
            $logo = EscposImage::load(ASSET_PATH . "img" . DIRECTORY_SEPARATOR . $name . ".png");
            $printer->bitImage($logo);
        } catch (\Exception $e) {
            $printer->text("Unable to print image");
        }

        if ($feed > 0)
            $printer->feed($feed);
    }
}