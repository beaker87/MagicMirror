import time
import struct
import socket
import hashlib
import base64
import sys
from select import select
import re
import logging
from threading import Thread
import signal
import RPi.GPIO as gpio
import picamera
import Queue
import os
from pwd import getpwnam

# Simple WebSocket server implementation. Handshakes with the client then echos back everything
# that is received. Has no dependencies (doesn't require Twisted etc) and works with the RFC6455
# version of WebSockets. Tested with FireFox 16, though should work with the latest versions of
# IE, Chrome etc.
#
# rich20b@gmail.com
# Adapted from https://gist.github.com/512987 with various functions stolen from other sites, see
# below for full details.

# Constants
MAGICGUID = "258EAFA5-E914-47DA-95CA-C5AB0DC85B11"
TEXT = 0x01
BINARY = 0x02

BUTTON_A_IN = 17
BUTTON_B_IN = 27
BUTTON_C_IN = 22
BUTTON_D_IN = 23

#set up pins 17 & 27 as inputs
gpio.setmode(gpio.BCM)
gpio.setup(BUTTON_A_IN, gpio.IN)
gpio.setup(BUTTON_B_IN, gpio.IN)
gpio.setup(BUTTON_C_IN, gpio.IN)
gpio.setup(BUTTON_D_IN, gpio.IN)

button_a_last_rise = 0
button_b_last_rise = 0
button_c_last_rise = 0
button_d_last_rise = 0
button_a_last_event = -1
button_b_last_event = -1
button_c_last_event = -1
button_d_last_event = -1

# set up queue
queue = Queue.Queue()

# Button handler thread
#def ButtonHandler():
#	global run
#	print("Button thread started!")
#	while run:
#		print("run is %r" % run )
#		time.sleep(5)
#		q.put("Ben Test Q Msg")

# Global camera object
#camera = None

# WebSocket implementation
class WebSocket(object):

    handshake = (
        "HTTP/1.1 101 Web Socket Protocol Handshake\r\n"
        "Upgrade: WebSocket\r\n"
        "Connection: Upgrade\r\n"
        "Sec-WebSocket-Accept: %(acceptstring)s\r\n"
        "Server: TestTest\r\n"
        "Access-Control-Allow-Origin: http://localhost\r\n"
        "Access-Control-Allow-Credentials: true\r\n"
        "\r\n"
    )

    camera = None

    # Constructor
    def __init__(self, client, server, msgqueue):
        self.client = client
        self.server = server
        self.handshaken = False
        self.header = ""
        self.data = ""
        self.msgqueue = msgqueue

    # Destructor
    def __del__(self):
		print("WebSocket destructor")

    def startcamera(self):
        if self.camera is None:
            print("Starting camera")
            self.camera = picamera.PiCamera()
            self.camera.start_preview()
        print("Camera started")

    def stopcamera(self):
        if self.camera is not None:
            print("Stopping camera")
            self.camera.stop_preview()
            self.camera.close()
            self.camera = None
        print("Camera stopped")

    # Serve this client
    def feed(self, data):
    
        self.running = True
    
        # If we haven't handshaken yet
        if not self.handshaken:
            logging.debug("No handshake yet")
            self.header += data
            if self.header.find('\r\n\r\n') != -1:
                parts = self.header.split('\r\n\r\n', 1)
                self.header = parts[0]
                if self.dohandshake(self.header, parts[1]):
                    logging.info("Handshake successful")
                    self.handshaken = True

        # We have handshaken
        else:
            logging.debug("Handshake is complete")
            
            # Decode the data that we received according to section 5 of RFC6455
            recv = self.decodeCharArray(data)

            m_msg = ''.join(recv).strip()
            print( "Message we got was %s" % m_msg)

            if m_msg == "":
                # We got an empty message - this can happen
                # when the client closes connection unexpectedly
                print("Message was empty, returning...")
                if self.camera is not None:
                    print("Camera still alive, closing it...")
                    self.stopcamera()
                return

            tkns = m_msg.split()           

            if tkns[0] == "keepalive":
                print("Port %d keepalive" % self.server.port)

            if tkns[0] == "ready":
                print( "Webpage is ready, waiting for external events" )

                # Clear all events on the queue, in case any were raised before we were ready.
                with self.msgqueue.mutex:
                    self.msgqueue.queue.clear()
                
                while self.running:
                    mmsg = self.msgqueue.get()
                    
                    cmd = mmsg.split()
                    
                    print( "WebSocket - got thread message: {0} (self.running is {1})".format(mmsg, self.running ))
                    
                    if cmd[0] == "IMG_UPLOAD":
                        print("New image uploaded: %s" % cmd[1])
                        txm = ''.join(mmsg).strip()
                        print("Sending %s to webpage" % txm)
                        self.sendMessage(txm)
                        
                        imgtodel = "uploads/%s" % cmd[1]
                        
                        time.sleep(5)

                        print("Deleting %s" % imgtodel)

                        # Delete image
                        os.remove( imgtodel )

                    if cmd[0] == "BUT_A_DOWN":
                        print("Button A has been pressed")
                        self.sendMessage(''.join(mmsg).strip())

                    if cmd[0] == "BUT_A_HOLD":
                        print("Button A has been released - it was held > 3 secs")
                        self.sendMessage(''.join(mmsg).strip())
                        
                        #TODO - some other function. 

                    if cmd[0] == "BUT_A":
                        print("Button A has been released")
                        self.sendMessage(''.join(mmsg).strip())

                    if cmd[0] == "BUT_B_DOWN":
                        print("Button B has been pressed")
                        self.sendMessage(''.join(mmsg).strip())

                    if cmd[0] == "BUT_B_HOLD":
                        print( "Button B has been released - it was held > 3 secs" )
                        self.sendMessage(''.join(mmsg).strip())

                        #Record a video!
                        print("Recording video...")
                        self.startcamera()
                        self.camera.resolution = (640, 480)
                        self.camera.start_recording('bentest.h264')
                        self.camera.wait_recording(10)
                        self.camera.stop_recording()
                        self.stopcamera()

                    if cmd[0] == "BUT_B":
                        if self.camera is None:
                            self.startcamera()
                            time.sleep(3)

                            timestamp = int(time.time())
                            filename = 'capture_%d.jpg' % timestamp
                            thumbfilename = 'capture_%d_thumb.jpg' % timestamp
                            capfilename = "uploads/%s" % filename

                            # Take the picture
                            self.camera.capture(capfilename)
                            
                            print("DONE")

                            self.stopcamera()

                            # Not pretty, but we need to chown this new file to www-data
                            # or php won't have access to be able to resize / copy it
                            myuid = getpwnam('www-data').pw_uid
                            os.chown(capfilename, myuid, -1)

                            txmsg = "BUT_B %s" % filename
                            #self.msgqueue.put(qmsg)
                            
                            print("Sending %s to webpage" % txmsg)
                            self.sendMessage(''.join(txmsg).strip());

                            imgtodel = "uploads/%s" % thumbfilename

                            imgDelTimeout = 30
                            fileDoesNotExist = True

                            if os.path.isfile(imgtodel):
                                fileDoesNotExist = False

                            while (fileDoesNotExist and (imgDelTimeout > 0)):
                                if os.path.isfile(imgtodel):
                                    fileDoesNotExist = False
                                else:
                                    print("%s does not exist yet, waiting..." % imgtodel)
                                    imgDelTimeout = imgDelTimeout - 1;
                                    time.sleep(1)

                            if fileDoesNotExist == False:                                
                                print("Deleting %s after 3 secs..." % imgtodel)
                                time.sleep(3)
                                # Delete image
                                os.remove( imgtodel )
                                print("%s deleted" % imgtodel)
                            else:
                                print("Timed out waiting for %s to exist!!" % imgtodel)
                                
                        else:
                            self.stopcamera()

                    self.msgqueue.task_done()

                print("Done with socket")

            if tkns[0] == "image_upload":

                print("Image has been uploaded - filename %s" % tkns[1])
                imgmsg = "IMG_UPLOAD %s" % tkns[1]
                self.msgqueue.put(imgmsg)
                self.sendMessage("done")           

            if tkns[0] == "camera":
                if tkns[1] == "start":
                    if self.camera is None:
                        self.startcamera()
                    else:
                        print("Nothing to do, camera already started")

                if tkns[1] == "stop":
                    if self.camera is None:
                        print("Nothing to do, camera already stopped")
                    else:
                        self.stopcamera()

                if tkns[1] == "picture":
                    if self.camera is not None:
                        timestamp = int(time.time())
                        filename = 'capture_%d.jpg' % timestamp
                        thumbfilename = 'capture_%d_thumb.jpg' % timestamp
                        capfilename = "uploads/%s" % filename

                        self.camera.capture(capfilename)
                        self.stopcamera()

                        # Not pretty, but we need to chown this new file to www-data
                        # or php won't have access to be able to resize / copy it
                        myuid = getpwnam('www-data').pw_uid
                        os.chown(capfilename, myuid, -1)

                        txmsg = "capture %s" % filename
                        #self.msgqueue.put(qmsg)

                        imgtowait = "uploads/%s" % thumbfilename
                      
                        time.sleep(2)

                        print("Sending %s to webpage" % txmsg)
                        self.sendMessage(''.join(txmsg).strip());
                        
                if tkns[1] == "brightness":
                    if self.camera is not None:
                        self.camera.brightness = int(tkns[2])

                if tkns[1] == "contrast":
                    if self.camera is not None:
                        self.camera.contrast = int(tkns[2])

                if tkns[1] == "saturation":
                    if self.camera is not None:
                        self.camera.saturation = int(tkns[2])

                if tkns[1] == "sharpness":
                    if self.camera is not None:
                        self.camera.sharpness = int(tkns[2])

                if tkns[1] == "image_effect":
                    if self.camera is not None:
                        try:
                            print("Setting camera effect: %s" % tkns[2])
                            self.camera.image_effect = tkns[2]
                        except:
                            print("That effect did not work! Exception = %s" % sys.exc_info()[0])
                            pass

                if tkns[1] == "awb_mode":
                    if self.camera is not None:
                        try:
                            print("Setting AWB mode: %s" % tkns[2])
                            self.camera.awb_mode = tkns[2]
                        except:
                            print("AWB mode %s did not work!" % tkns[2])
                            pass

                if tkns[1] == "exposure_mode":
                    if self.camera is not None:
                        try:
                            print("Setting exposure mode: %s" % tkns[2])
                            self.camera.exposure_mode = tkns[2]
                        except:
                            print("Exposure mode %s did not work!" % tkns[2])
                            pass

                if tkns[1] == "meter_mode":
                    if self.camera is not None:
                        try:
                            print("Setting meter mode: %s" % tkns[2])
                            self.camera.meter_mode = tkns[2]
                        except:
                            print("Meter mode %s did not work!" % tkns[2])
                            pass
						

            #else:
                # Send our reply
            #    logging.debug("Sending message...")
            #    self.sendMessage(''.join(recv).strip());

            #self.sendMessage(''.join(tx_msg).strip());

    # Stolen from http://www.cs.rpi.edu/~goldsd/docs/spring2012-csci4220/websocket-py.txt
    def sendMessage(self, s):
        """
        Encode and send a WebSocket message
        """

        # Empty message to start with
        message = ""
        
        # always send an entire message as one frame (fin)
        b1 = 0x80

        # in Python 2, strs are bytes and unicodes are strings
        if type(s) == unicode:
            b1 |= TEXT
            payload = s.encode("UTF8")
            
        elif type(s) == str:
            b1 |= TEXT
            payload = s

        # Append 'FIN' flag to the message
        message += chr(b1)

        # never mask frames from the server to the client
        b2 = 0
        
        # How long is our payload?
        length = len(payload)
        if length < 126:
            b2 |= length
            message += chr(b2)
        
        elif length < (2 ** 16) - 1:
            b2 |= 126
            message += chr(b2)
            l = struct.pack(">H", length)
            message += l
        
        else:
            l = struct.pack(">Q", length)
            b2 |= 127
            message += chr(b2)
            message += l

        # Append payload to message
        message += payload

        # Send to the client
        self.client.send(str(message))


    # Stolen from http://stackoverflow.com/questions/8125507/how-can-i-send-and-receive-websocket-messages-on-the-server-side
    def decodeCharArray(self, stringStreamIn):
    
        # Turn string values into opererable numeric byte values
        byteArray = [ord(character) for character in stringStreamIn]
        datalength = byteArray[1] & 127
        indexFirstMask = 2

        if datalength == 126:
            indexFirstMask = 4
        elif datalength == 127:
            indexFirstMask = 10

        # Extract masks
        masks = [m for m in byteArray[indexFirstMask : indexFirstMask+4]]
        indexFirstDataByte = indexFirstMask + 4
        
        # List of decoded characters
        decodedChars = []
        i = indexFirstDataByte
        j = 0
        
        # Loop through each byte that was received
        while i < len(byteArray):
        
            # Unmask this byte and add to the decoded buffer
            decodedChars.append( chr(byteArray[i] ^ masks[j % 4]) )
            i += 1
            j += 1

        # Return the decoded string
        return decodedChars


    # Handshake with this client
    def dohandshake(self, header, key=None):
    
        logging.debug("Begin handshake: %s" % header)
        
        # Get the handshake template
        handshake = self.handshake
        
        # Step through each header
        for line in header.split('\r\n')[1:]:
            name, value = line.split(': ', 1)
            
            # If this is the key
            if name.lower() == "sec-websocket-key":
            
                # Append the standard GUID and get digest
                combined = value + MAGICGUID
                response = base64.b64encode(hashlib.sha1(combined).digest())
                
                # Replace the placeholder in the handshake response
                handshake = handshake % { 'acceptstring' : response }

        logging.debug("Sending handshake %s" % handshake)
        self.client.send(handshake)
        return True

    def onmessage(self, data):
        logging.debug("Got message: %s" % data)
        self.send(data)

    def send(self, data):
        logging.debug("Sent message: %s" % data)
        self.client.send("\x00%s\xff" % data)

    def close(self):
        self.client.close()


# WebSocket server implementation
class WebSocketServer(object):

    # Constructor
    def __init__(self, bind, port, cls):
        self.socket = socket.socket(socket.AF_INET, socket.SOCK_STREAM)
        self.socket.setsockopt(socket.SOL_SOCKET, socket.SO_REUSEADDR, 1)
        self.socket.bind((bind, port))
        self.bind = bind
        self.port = port
        self.cls = cls
        self.connections = {}
        self.listeners = [self.socket]

    # Listen for requests
    def listen(self, backlog, msgqueue):

        self.socket.listen(backlog)
        logging.info("Listening on %s" % self.port)

        # Keep serving requests
        self.running = True
        while self.running:
        
            # Find clients that need servicing
            rList, wList, xList = select(self.listeners, [], self.listeners, 1)
            for ready in rList:
                if ready == self.socket:
                    logging.debug("New client connection")
                    client, address = self.socket.accept()
                    fileno = client.fileno()
                    self.listeners.append(fileno)
                    self.connections[fileno] = self.cls(client, self, msgqueue)
                else:
                    logging.debug("Client ready for reading %s" % ready)
                    client = self.connections[ready].client
                    data = client.recv(4096)
                    fileno = client.fileno()
                    if data:
                        self.connections[fileno].feed(data)
                    else:
                        logging.debug("Closing client %s" % ready)
                        self.connections[fileno].close()
                        del self.connections[fileno]
                        self.listeners.remove(ready)
            
            # Step though and delete broken connections
            for failed in xList:
                if failed == self.socket:
                    logging.error("Socket broke")
                    for fileno, conn in self.connections:
                        conn.close()
                    self.running = False

        print("Telling cls to stop")
        self.cls.running = False

def buttona_handler(BUTTON_A_IN):
    global button_a_last_rise
    global button_a_last_event
    ts = int(time.time())

    button_event = gpio.input(BUTTON_A_IN)

    if ( button_a_last_rise == 0 ):
        button_a_last_rise = ts

    if ( button_a_last_event != button_event ): # Only detect changes
        button_a_last_event = button_event
        if button_event:  
            #print ("Falling edge detected on %d" % BUTTON_A_IN)
            diff = ts - button_a_last_rise
            #print ("ts is %d" % ts)
            #print ("button_a_last_rise is %d" % button_a_last_rise)
            #print ("diff is %d" % diff)
            if ( diff >= 3 ):
                tx_msg = "BUT_A_HOLD"
                print("[handler] Button A was held for %d secs" % diff)
            else:
                tx_msg = "BUT_A"
                print("[handler] Button A was pressed!")
            queue.put(tx_msg)

        else:
            #print ("Rising edge detected on %d" % BUTTON_A_IN)
            if button_a_last_rise != ts:
                button_a_last_rise = ts
                queue.put("BUT_A_DOWN")

def buttonb_handler(BUTTON_B_IN):
    global button_b_last_rise
    global button_b_last_event
    ts = int(time.time())

    button_event = gpio.input(BUTTON_B_IN)

    if ( button_b_last_rise == 0 ):
        button_b_last_rise = ts

    if ( button_b_last_event != button_event ): # Only detect changes
        button_b_last_event = button_event
        if button_event:  
            #print ("Falling edge detected on %d" % BUTTON_B_IN)
            diff = ts - button_b_last_rise
            #print ("ts is %d" % ts)
            #print ("button_b_last_rise is %d" % button_b_last_rise)
            #print ("diff is %d" % diff)
            if ( diff >= 3 ):
                tx_msg = "BUT_B_HOLD"
                print("[handler] Button B was held for %d secs" % diff)
            else:
                tx_msg = "BUT_B"
                print("[handler] Button B was pressed!")
            queue.put(tx_msg)

        else:
            #print ("Rising edge detected on %d" % BUTTON_B_IN)
            if button_b_last_rise != ts:
                button_b_last_rise = ts
                queue.put("BUT_B_DOWN")

def buttonc_handler(BUTTON_C_IN):
    global button_c_last_rise
    global button_c_last_event
    ts = int(time.time())

    button_event = gpio.input(BUTTON_C_IN)

    if ( button_c_last_rise == 0 ):
        button_c_last_rise = ts

    if ( button_c_last_event != button_event ): # Only detect changes
        button_c_last_event = button_event
        if button_event:  
            #print ("Falling edge detected on %d" % BUTTON_A_IN)
            diff = ts - button_c_last_rise
            #print ("ts is %d" % ts)
            #print ("button_c_last_rise is %d" % button_c_last_rise)
            #print ("diff is %d" % diff)
            if ( diff >= 3 ):
                tx_msg = "BUT_C_HOLD"
                print("[handler] Button C was held for %d secs" % diff)
            else:
                tx_msg = "BUT_C"
                print("[handler] Button C was pressed!")
            queue.put(tx_msg)

        else:
            #print ("Rising edge detected on %d" % BUTTON_C_IN)
            if button_c_last_rise != ts:
                button_c_last_rise = ts
                queue.put("BUT_C_DOWN")

def buttond_handler(BUTTON_D_IN):
    global button_d_last_rise
    global button_d_last_event
    ts = int(time.time())

    button_event = gpio.input(BUTTON_D_IN)

    if ( button_d_last_rise == 0 ):
        button_d_last_rise = ts

    if ( button_d_last_event != button_event ): # Only detect changes
        button_d_last_event = button_event
        if button_event:  
            #print ("Falling edge detected on %d" % BUTTON_D_IN)
            diff = ts - button_d_last_rise
            #print ("ts is %d" % ts)
            #print ("button_d_last_rise is %d" % button_d_last_rise)
            #print ("diff is %d" % diff)
            if ( diff >= 3 ):
                tx_msg = "BUT_D_HOLD"
                print("[handler] Button D was held for %d secs" % diff)
            else:
                tx_msg = "BUT_D"
                print("[handler] Button D was pressed!")
            queue.put(tx_msg)

        else:
            #print ("Rising edge detected on %d" % BUTTON_D_IN)
            if button_d_last_rise != ts:
                button_d_last_rise = ts
                queue.put("BUT_D_DOWN")


#        tx_msg = "BUT_A"
#        print("[handler] Button A pressed!")
#        queue.put(tx_msg)


##def buttona_fall_handler(BUTTON_A_IN):
##    global button_a_last_rise
##    ts = int(time.time())
##    diff = ts - button_a_last_rise
##    if ( diff > 0 ):
##        if ( diff >= 3 ):
##            tx_msg = "BUT_A_HOLD"
##            print("[handler] Button A was held for %d secs" % diff)
##        else:
##            tx_msg = "BUT_A"
##            print("[handler] Button A was pressed!")
##        queue.put(tx_msg)
##
##def buttonb_fall_handler(BUTTON_B_IN):
##    global button_b_last_rise
##    ts = int(time.time())
##    diff = ts - button_b_last_rise
##    if ( diff > 0 ):
##        if ( diff >= 3 ):
##            tx_msg = "BUT_B_HOLD"
##            print("[handler] Button B was held for %d secs" % diff)
##        else:
##            tx_msg = "BUT_B"
##            print("[handler] Button B was pressed!")
##        queue.put(tx_msg)


# Entry point
if __name__ == "__main__":

    logging.basicConfig(level=logging.DEBUG, format="%(asctime)s - %(levelname)s - %(message)s")

    # Start mirror server
    server = WebSocketServer("", 9999, WebSocket)
    server_thread = Thread(target=server.listen, args=[5,queue])

    # Start upload server
    upload_server = WebSocketServer("", 9998, WebSocket)
    upload_server_thread = Thread(target=upload_server.listen, args=[5,queue])
    
    print("Starting main thread")
    server_thread.daemon = True
    server_thread.start()

    print("Starting upload thread")
    upload_server_thread.daemon = True
    upload_server_thread.start()

    # Register button event handlers
    gpio.add_event_detect(BUTTON_A_IN, gpio.BOTH, callback=buttona_handler)
    gpio.add_event_detect(BUTTON_B_IN, gpio.BOTH, callback=buttonb_handler)
    gpio.add_event_detect(BUTTON_C_IN, gpio.BOTH, callback=buttonc_handler)
    gpio.add_event_detect(BUTTON_D_IN, gpio.BOTH, callback=buttond_handler)
    
    
    # Add SIGINT handler for killing the threads
    def signal_handler(signal, frame):
        logging.info("Caught Ctrl+C, shutting down...")
        #queue.join()
        server.running = False
        upload_server.running = False
        print("Terminating cls")
        server.cls.running = False
        print( "Cleaning up GPIO..." )
        gpio.cleanup()
        print("Exiting")
        sys.exit()
    signal.signal(signal.SIGINT, signal_handler)

    while True:
        time.sleep(100)
