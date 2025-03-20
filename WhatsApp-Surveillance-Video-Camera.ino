         ////////////////////////////////////////////////////  
        //      WhatsApp Surveillance Video Camera        //
       //        with Infrared Proximity Sensor          //
      //           -------------------------            //
     //                 Arduino Nano                   //           
    //               by Kutluhan Aktar                // 
   //                                                //
  ////////////////////////////////////////////////////

// Get notified via WhatsApp with a video and a captured first-look thumbnail as intrusion alert when the proximity sensor detects motion.
//
// This code is only for reading analog inputs in order to pass the values to Raspberry Pi.
//
// For more information, visit the project page:
// https://www.theamplituhedron.com/projects/WhatsApp-Surveillance-Video-Camera-with-Infrared-Proximity-Sensor/
//
// If you need a host server for this project or a web application by which you can manage the uploaded files easily, check out this application on TheAmplituhedron:
// https://www.theamplituhedron.com/dashboard/WhatsApp-Surveillance-Camera/
//
// Connections
// Arduino Nano :           
//                                Sharp-GP2Y0A02YK0F IR Sensor
// GND --------------------------- GND
// 5V  --------------------------- +
// A0  --------------------------- Signal
//
//                                Photoresistor
// A1  --------------------------- 
//                                IR_OUTPUT_LED
// D3  --------------------------- 
//                                PR_OUTPUT_LED
// D4  --------------------------- 


// Define sensor pins.
#define IR_PIN A0
#define PR_PIN A1
#define IR_OUTPUT 3
#define PR_OUTPUT 4

// Define data holders.
int distance, brightness;

void setup() {
  Serial.begin(9600);

  pinMode(IR_OUTPUT, OUTPUT);
  pinMode(PR_OUTPUT, OUTPUT);
  
}

void loop() {
  // Get brightness.
  brightness = map(analogRead(PR_PIN), 0, 1023, 0, 100);
  
  // Get distance from Sharp-GP2Y0A02YK0F IR Sensor.
  float volts = analogRead(IR_PIN) * 0.0048828125; // Sensor Value: (5 / 1024)
  distance = 13 * pow(volts, -1); //from datasheet graph
  delay(100);

  Serial.println("Brightness: " + String(brightness) + "%\n\n");
  Serial.println("Distance: " + String(distance) + "cm");

  // Send signal to Raspberry Pi.
  if(distance < 9){ digitalWrite(IR_OUTPUT, HIGH); }else{ digitalWrite(IR_OUTPUT, LOW); }

  if(brightness < 10){digitalWrite(PR_OUTPUT, HIGH); }else{ digitalWrite(PR_OUTPUT, LOW); }
}
