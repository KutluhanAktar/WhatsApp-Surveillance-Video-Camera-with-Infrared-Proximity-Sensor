# WhatsApp Surveillance Video Camera with Infrared Proximity Sensor
#
# Raspberry Pi 3 Model B+
#
# By Kutluhan Aktar
#
# Get notified via WhatsApp with a video and a captured first-look thumbnail as intrusion alert
# when the proximity sensor detects motion.
#
# For more information and explanation, visit the link below:
# https://www.theamplituhedron.com/projects/WhatsApp-Surveillance-Video-Camera-with-Infrared-Proximity-Sensor/
#
# If you need a host server for this project or a web application by which you can manage the uploaded files easily,
# check out this application on TheAmplituhedron:
# https://www.theamplituhedron.com/dashboard/WhatsApp-Surveillance-Camera/

from picamera import PiCamera
from time import sleep
import datetime
from subprocess import call 
import requests
import RPi.GPIO as GPIO

# Set up BCM GPIO numbering
GPIO.setmode(GPIO.BCM)
# Set up input pins
IR_INPUT = 22
PR_INPUT = 23
GPIO.setup(IR_INPUT, GPIO.IN)
GPIO.setup(PR_INPUT, GPIO.IN)

# Define functions:

# Initiate the camera module with pre-defined setttings.
camera = PiCamera()
camera.resolution = (640, 480)
camera.framerate = 15

def record_trespassing(file_h264, file_mp4, file_capture, text):
    # Add date as timestamp on the generated files.
    camera.annotate_text = text
    # Capture an image as the thumbnail.
    sleep(2)
    camera.capture(file_capture)
    print("\r\nImage Captured! \r\n")
    # Record a 15 seconds video.
    camera.start_recording(file_h264)
    sleep(20)
    camera.stop_recording()
    print("Rasp_Pi => Video Recorded! \r\n")
    # Convert the h224 format to the mp4 format.
    command = "MP4Box -add " + file_h264 + " " + file_mp4
    call([command], shell=True)
    print("\r\nRasp_Pi => Video Converted! \r\n")
    
def send_video_to_server(file_mp4, file_capture, brightness):
    # Define the file path to send the currently recorded video to the server.  
    url = 'https://www.theamplituhedron.com/dashboard/WhatsApp-Surveillance-Camera/file_pathway.php'
    files = {'rasp_video': open(file_mp4, 'rb'), 'rasp_capture': open(file_capture, 'rb')}
    data = {'brightness': brightness}
    # Make an HTTP Post Request to the server.
    request = requests.post(url, files=files, data=data)
    # Print the response from the server.
    print ("Rasp_Pi => Files Transferred! \r\n")
    print(request.text + "\r\n")

# Initiate the loop.
while True:
    print("Waiting...")
    
    if GPIO.input(IR_INPUT):
        brightness = "LOW" if GPIO.input(PR_INPUT) else "OPTIMUM"
        # Get the current date as the timestamp to generate unique file names.
        date = datetime.datetime.now().strftime('%m-%d-%Y_%H.%M.%S')
        capture_img = '/home/pi/Surveillance/intrusion_' + date + '.jpg'
        video_h264 = '/home/pi/Surveillance/intrusion_' + date + '.h264'
        video_mp4 = '/home/pi/Surveillance/intrusion_' + date + '.mp4'
        # Create and send files:
        record_trespassing(video_h264, video_mp4, capture_img, date)
        send_video_to_server(video_mp4, capture_img, brightness)
        
    sleep(1)