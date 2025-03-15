import wsApi from '../../utils/websocketApi'
//@ts-ignore
import classes from './MessengerInput.module.css'

import { useEffect, useRef, useState } from 'react'

function MessengerInput(props: any) {
	const [text, setText] = useState<string>('')
	const [send, goSend] = useState<number>(0)
	const textareaRef = useRef<HTMLTextAreaElement>(null)

	const handleChange = (event: React.ChangeEvent<HTMLTextAreaElement>) => {
		setText(event.target.value)

		if (textareaRef.current) {
			textareaRef.current.style.height = 'auto'
			textareaRef.current.style.height = `${textareaRef.current.scrollHeight}px`
		}
	}

	useEffect(() => {
		if (send !== 0) {
			wsApi.sendMessage(props.contactId, text, false)
			setText('')
		}
	}, [send])

	const handleClick = () => {
		goSend(send + 1)
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
