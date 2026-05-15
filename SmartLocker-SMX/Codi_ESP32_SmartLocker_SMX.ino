// SMARTLOCKER SMX
// Sistema de taquilles intel·ligents amb ESP32
#include <Wire.h>
#include <LiquidCrystal_I2C.h>
#include <Keypad.h>
#include <ESP32Servo.h>
#include <WiFi.h>
#include <HTTPClient.h>

String password = "";

const char* ssid = "Aula_104";
const char* wifiPassword = "aula104aula104";

LiquidCrystal_I2C lcd(0x27, 16, 2);

Servo servoA;
Servo servoB;

int pinA = 42;
int pinB = 41;

const byte numRows = 4;
const byte numCols = 4;

char keymap[numRows][numCols] = {
  {'1','2','3','A'},
  {'4','5','6','B'},
  {'7','8','9','C'},
  {'*','0','#','D'}
};

byte rowPins[numRows] = {15,16,17,18};
byte colPins[numCols] = {12,13,14,46};

Keypad myKeypad = Keypad(
  makeKeymap(keymap),
  rowPins,
  colPins,
  numRows,
  numCols
);

void mostrarMensaje(String linea1, String linea2 = "") {

  lcd.clear();

  lcd.setCursor(0, 0);
  lcd.print(linea1);

  lcd.setCursor(0, 1);
  lcd.print(linea2);
}

void abrirA() {

  Serial.println("Abriendo taquilla A");

  mostrarMensaje("Obrint...", "Taquilla A");

  // ABRIR
  servoA.write(110);
  delay(300);

  // PARAR
  servoA.write(90);

  // ESPERA ABIERTA
  delay(3000);

  mostrarMensaje("Tancant...", "Taquilla A");

  // CERRAR
  servoA.write(80);
  delay(300);

  // PARAR
  servoA.write(90);
}

void abrirB() {

  Serial.println("Abriendo taquilla B");

  mostrarMensaje("Obrint...", "Taquilla B");

  // ABRIR
  servoB.write(110);
  delay(300);

  // PARAR
  servoB.write(90);

  // ESPERA ABIERTA
  delay(3000);

  mostrarMensaje("Tancant...", "Taquilla B");

  // CERRAR
  servoB.write(80);
  delay(300);

  // PARAR
  servoB.write(90);
}

void setup() {

  Serial.begin(115200);

  Wire.begin(4, 5);

  lcd.init();
  lcd.backlight();

  servoA.attach(pinA);
  servoB.attach(pinB);

  servoA.write(90);
  servoB.write(90);

  delay(1000);

  mostrarMensaje("SmartLocker", "SMX");

  delay(2000);

  mostrarMensaje("Connectant", "WiFi...");

  WiFi.begin(ssid, wifiPassword);

  while (WiFi.status() != WL_CONNECTED) {

    delay(500);
    Serial.print(".");
  }

  Serial.println("");
  Serial.println("WiFi conectado");
  Serial.println(WiFi.localIP());

  mostrarMensaje("WiFi connectat", "[ OK ]");

  delay(2000);

  mostrarMensaje("PIN + # = OK");
  delay(2000);

  mostrarMensaje("* = Esborrar");
  delay(2000);

  mostrarMensaje("Introdueix PIN");
}

void loop() {

  char keypressed = myKeypad.getKey();

  if (keypressed) {

    Serial.print("Tecla: ");
    Serial.println(keypressed);

    if (isDigit(keypressed)) {

      if (password.length() < 4) {

        password += keypressed;

        lcd.setCursor(password.length() - 1, 1);
        lcd.print("*");
      }
    }

    else if (keypressed == '#') {

      mostrarMensaje("Validant PIN");

      Serial.print("PIN introducido: ");
      Serial.println(password);

      delay(500);

      if (WiFi.status() != WL_CONNECTED) {

        mostrarMensaje("Error WiFi", "Sense connexio");

        Serial.println("WiFi desconectado");

        delay(2500);
      }

      else {

        HTTPClient http;

        String url =
          "http://10.104.21.10/smartlockerSolutions/validar_pin.php?pin="
          + password;

        Serial.print("URL: ");
        Serial.println(url);

        http.begin(url);

        http.setTimeout(5000);

        int httpCode = http.GET();

        if (httpCode > 0) {

          String respuesta = http.getString();

          respuesta.trim();

          Serial.print("Respuesta servidor: ");
          Serial.println(respuesta);

          if (respuesta == "USER_A") {

            mostrarMensaje("PIN correcte", "[ OK ]");
            delay(2000);

            mostrarMensaje("Pots recollir", "el teu paquet");
            delay(2500);

            mostrarMensaje("Gracies!", "Taquilla A");
            delay(2000);

            abrirA();
          }

          else if (respuesta == "USER_B") {

            mostrarMensaje("PIN correcte", "[ OK ]");
            delay(2000);

            mostrarMensaje("Pots recollir", "el teu paquet");
            delay(2500);

            mostrarMensaje("Gracies!", "Taquilla B");
            delay(2000);

            abrirB();
          }

          else if (respuesta == "REP_A") {

            mostrarMensaje("PIN correcte", "[ OK ]");
            delay(2000);

            mostrarMensaje("Repartidor A", "Deixa paquet");
            delay(2500);

            mostrarMensaje("Gracies!", "Taquilla A");
            delay(2000);

            abrirA();
          }

          else if (respuesta == "REP_B") {

            mostrarMensaje("PIN correcte", "[ OK ]");
            delay(2000);

            mostrarMensaje("Repartidor B", "Deixa paquet");
            delay(2500);

            mostrarMensaje("Gracies!", "Taquilla B");
            delay(2000);

            abrirB();
          }
          
          else if (respuesta == "CAD_A") {

          mostrarMensaje("PIN correcte", "[ OK ]");
          delay(2000);

          mostrarMensaje("Paquet", "caducat");
          delay(2000);

          mostrarMensaje("Retira paquet", "Taquilla A");
          delay(2500);

          abrirA();
        }

        else if (respuesta == "CAD_B") {

          mostrarMensaje("PIN correcte", "[ OK ]");
          delay(2000);

          mostrarMensaje("Paquet", "caducat");
          delay(2000);

          mostrarMensaje("Retira paquet", "Taquilla B");
          delay(2500);

          abrirB();
        }

          else {

            mostrarMensaje("PIN incorrecte", "Torna-ho provar");

            Serial.println("PIN incorrecto");

            delay(3000);
          }
        }

        else {

          mostrarMensaje("Error servidor");

          Serial.print("HTTP Error: ");
          Serial.println(httpCode);

          delay(3000);
        }

        http.end();
      }

      password = "";

      mostrarMensaje("Introdueix PIN");
    }

    else if (keypressed == '*') {

      password = "";

      mostrarMensaje("PIN esborrat");

      Serial.println("PIN borrado");

      delay(2000);

      mostrarMensaje("Introdueix PIN");
    }
  }
}