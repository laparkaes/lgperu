import time
import pyautogui

class ctrl:
    
    # Mouse Control
    def mouse_info(self):
        pyautogui.mouseInfo()
    
    def move_to(self, x, y, sec = 0.1):
        pyautogui.moveTo(x, y)
        time.sleep(sec)
    
    def drag_to(self, x, y, sec = 0.1):
        pyautogui.dragRel(x, y)
        time.sleep(sec)
    
    def click(self, x, y, sec = 0.1):
        pyautogui.click(x, y)
        time.sleep(sec)

    def doble_click(self, x, y, sec = 0.1):
        pyautogui.click(x, y, clicks=2, interval=0.2)
        time.sleep(sec)

    # Keyboard Control
    def key_list(self):
        print(pyautogui.KEYBOARD_KEYS)
    
    def write(self, msg, sec = 0.1):
        pyautogui.write(msg)
        time.sleep(sec)


    def key_press(self, k, sec = 0.1):
        pyautogui.press(k)
        time.sleep(sec)

    def hotkey_press(self, k1, k2, sec = 0.1):
        pyautogui.hotkey(k1, k2)
        time.sleep(sec)

    # Msg Windows
    def alert(self, msg, t = "Alert", b = "Confirm"):
        pyautogui.alert(msg, title = t, button = b)

    def confirm(self, msg, t = "Confirm", b = ("Ok", "Cancel")):
        return pyautogui.confirm(msg, title = t, buttons = b)

    def prompt(self, msg, t = "Request"):
        res = pyautogui.prompt(msg, title = t)
        if res in [None, ""]: res = None
        return res
    
    
    
