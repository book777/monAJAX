<?php

class MinecraftServer
{// Class written by xPaw & modded by book777

    const STATISTIC = 0x00;
    const HANDSHAKE = 0x09;
    private $socket;

    function getp($address, $timeout = 3)
    {
        $thetime = microtime(true);
        $in = @fsockopen($address, 25565, $errno, $errstr, $timeout);
        $ping = round((microtime(true) - $thetime) * 1000);
        if (!$in) return array(
            'address' => $address,
            'ping' => $ping,
            'status' => 'Выключен'
        );
        if ($ping > $timeout * 1000)
            return array(
                'address' => $address,
                'ping' => $ping,
                'status' => 'Большой пинг');
        @stream_set_timeout($in, $timeout);
        fwrite($in, "\xFE\x01");
        $data = fread($in, 4096);
        $Len = strlen($data);
        if ($Len < 4 || $data[0] !== "\xFF")
            return array(
                'address' => $address,
                'status' => 'Неизвестное ядро'
            );
        $data = substr($data, 3);
        $data = iconv('UTF-16BE', 'UTF-8', $data);
        if ($data [1] === "\xA7" && $data[2] === "\x31") {
            $data = explode("\x00", $data);
            return array(
                'address' => $address,
                'status' => 'online',
                'online' => intval($data[4]),
                'motd' => $this->motd($data[3]),
                'type' => $data[0],
                'slots' => intval($data[5]),
                'percent' => @floor((intval($data[4]) / intval($data[5])) * 100),
                'version' => $data[2],
                'ping' => $ping
            );
        }
        $data = explode("\xA7", $data);
        return array(
            'address' => $address,
            'status' => 'online',
            'online' => isset($data[1]) ? intval($data[1]) : 0,
            'slots' => isset($data[2]) ? intval($data[2]) : 0,
            'percent' => @floor((intval($data[1]) / intval($data[2])) * 100),
            'version' => '< 1.4',
            'ping' => $ping
        );
    }

    function getq($address, $timeout = 3)
    {// Получение данных через query
        $thetime = microtime(true);
        $this->socket = @fsockopen('udp://' . $address, 25565, $ErrNo, $ErrStr, $timeout);
        $info['ping'] = round((microtime(true) - $thetime) * 1000);
        if ($this->socket === false)
            return array(
                'status' => 'Выключен',
                'address' => $address
            );
        stream_set_timeout($this->socket, $timeout);
        stream_set_blocking($this->socket, true);
        $Challenge = $this->GetChallenge();
        $data = $this->writedata(self::STATISTIC, $Challenge . pack('c*', 0x00, 0x00, 0x00, 0x00));
        if (!$data)
            return $this->getp($address, $timeout);// Пробуем получить данные обычным способом
        fclose($this->socket);
        $data = substr($data, 11);
        $data = explode("\x00\x00\x01player_\x00\x00", $data);
        if (count($data) !== 2)
            return array
            (
                'status' => 'Хостинг не поддерживает такую дешифрацию',// Для решения проблемы нужны тексты на таком хостинге - vk.me/nikolia0612
                'address' => $address
            );
        $info['names'] = explode("\x00", substr($data[1], 0, -2));
        $data = explode("\x00", $data[0]);
        $Keys = array(
            'numplayers' => 'online',
            'maxplayers' => 'slots',
            'hostname' => 'motd',
            'version' => 'version',
            'gametype' => 'type',
            'game_id' => 'game',
            'plugins' => 'plugins',
            'map' => 'map'
        );
        /*if($info['plugins']) {
            $data = explode(': ', $info['plugins'], 2);
            $info['core'] = $data[0];
            if(sizeof($data) == 2)
            $info['plugins'] = explode('; ', $data[1]);
        } else {
            $info['core'] = $info['plugins'];
            unset($info['plugins']);
        }*/
        $Last = '';
        foreach ($data as $Key => $Value) {
            if (~$Key & 1) {
                if (!array_key_exists($Value, $Keys)) {
                    $Last = false;
                    continue;
                }
                $Last = $Keys[$Value];
                $info[$Last] = '';
            } else if ($Last != false)
                $info[$Last] = $Value;
        }
        $info += array(
            'status' => 'online',
            'address' => $address,
            'online' => intval($info['online']),
            'slots' => intval($info['slots']),
            'percent' => @floor(($info['online'] / $info['slots']) * 100)
        );
        $info['motd'] = filter_var($info['motd'], FILTER_SANITIZE_STRING, FILTER_FLAG_STRIP_HIGH);// Уберет неизветные символы (без этого может !json_encode())
        return $info;
    }

    private function writedata($command, $Append = '')
    {
        $command = pack('c*', 0xFE, 0xFD, $command, 0x01, 0x02, 0x03, 0x04) . $Append;
        $Length = strlen($command);
        if ($Length !== fwrite($this->socket, $command, $Length))
            return array(
                'status' => 'Неудачный запрос'
            );
        $data = fread($this->socket, 4096);
        if ($data === false)
            return array(
                'status' => 'Не удалось прочитать ответ'
            );
        if (strlen($data) < 5 || $data[0] != $command[2])
            return false;
        return substr($data, 5);
    }

    private function motd($text)
    {
        $mass = explode('§', $text);
        $out = '';
        foreach ($mass as $val)
            $out .= substr($val, 1);
        return $out;
    }

    private function GetChallenge()
    {
        $data = $this->writedata(self :: HANDSHAKE);
        return pack('N', $data);
    }
}
