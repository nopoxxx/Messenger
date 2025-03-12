//@ts-ignore
import classes from './MessengerInput.module.css'

import { useRef, useState } from 'react'

function MessengerInput() {
	const [text, setText] = useState<string>('')
	const textareaRef = useRef<HTMLTextAreaElement>(null)

	const handleChange = (event: React.ChangeEvent<HTMLTextAreaElement>) => {
		setText(event.target.value)

		if (textareaRef.current) {
			textareaRef.current.style.height = 'auto'
			textareaRef.current.style.height = `${textareaRef.current.scrollHeight}px`
		}
	}

	const handleClick = () => {
		alert('Кнопка нажата!')
	}

	return (
		<div className={classes.MessengerInput}>
			<div className={classes.container}>
				<textarea
					ref={textareaRef}
					value={text}
					onChange={handleChange}
					className='textarea'
				/>
				<img
					className={classes.sendButton}
					src={require('../../images/SendButton.png')}
					alt='Send'
					onClick={handleClick}
					draggable='false'
				/>
			</div>
		</div>
	)
}

export default MessengerInput
