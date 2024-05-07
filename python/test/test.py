
import my_control

ctrl = my_control.ctrl()

ctrl.hotkey_press("alt", "tab", 2)
ctrl.hotkey_press("win", "r", 2)
ctrl.write("hola como estas? estoy trabajando y tu?", 2)

ctrl.alert("Terminaste", "Yeeeeeeeee", "Confirmar")


"""


ctrl.alert("hola mundo", "Hello world", "Confirmar")

confirm_txt = ctrl.confirm("Hola confirm", "Confirmame esta ventana", ("Entendido", "No quiero"))
print("resultado de ventana de confirmacion: " + confirm_txt)

prompt_txt = ctrl.prompt("Insert your OTP", "OTP Request")
if prompt_txt != None: 
    print("Valor que recibi" + prompt_txt)

ctrl.mouse_info()
ctrl.click(700, 700, 1)
ctrl.move_to(500, 500, 5)
ctrl.doble_click(600, 600, 2)

pyautogui.hotkey("winleft", "d")
time.sleep(1)

pyautogui.press('winleft')
time.sleep(1)

pyautogui.write('notepad')
time.sleep(1)

pyautogui.press('enter')
time.sleep(1)
"""
#pyautogui.hotkey("winleft", "d")

#pyautogui.write('Hello world!')

#pyautogui.click(500, 500)


